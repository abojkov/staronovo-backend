<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\Post;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App;
use PDOException;

class UserController extends Controller
{

    public function getAll(){
        $users = User::all();

        foreach ($users as $user){
            unset($user['password']);
        }

        return response()->json($users);
    }

    public function get(Request $request){
        // Validation
        $this->validate($request, [
            'username' => 'required'
        ], \ValidatorMessages::messages);

        // ID за админ е 1
        $item = User::select('id', 'username', 'name', 'surname')->where('username', 'LIKE', '%'.$request['username'].'%')->where('role_id', '!=', 1)->where('is_active', '=', 1)->get();

        return response()->json($item);
    }

    public function create(Request $request){
        // Validation
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required',
            'password' => 'required',
            'repeatPass' => 'required|same:password',
            'name' => 'required',
            'surname' => 'required'
        ], \ValidatorMessages::messages);

        // Further validation
        if(User::where('username', '=', mb_strtolower($request['username']))->first() != null){
            // Exists with same username
            $validator->errors()->add(
                'username', 'Корисничкото име е зафатено'
            );
        }
        if(User::where('email', '=', mb_strtolower($request['email']))->first() != null){
            // Exists with same username
            $validator->errors()->add(
                'email', 'Email адресата постои!'
            );
        }

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 409);
        }

        // Default population
        $request['is_active'] = true;
        $request['role_id'] = RoleController::internal_get('ROLE_USER');
        $request['password'] = Hash::make($request['password'], [
            'rounds' => 12,
        ]);

        // Insert
        $item = User::create($request->all());

        return response()->json($item, 201);
    }

    public function update($id, Request $request){
        if(AuthController::internal_getLoggedInUser()['id'] != $id){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        if(count($request->all()) == 0){
            return response()->json(array('message' => 'Не постојат информации кои можат да се променат!'), 422);
        }

        $updated = $this->internal_populateUser($id, $request->all());
        $errors = $updated['errors'];
        $updated = $updated['item']->getAttributes();

        if(sizeof($errors)>0){
            // Има грешки
            return response()->json($errors, 422);
        }


        // Insert
        $item = User::find($id);
        $item->update($updated);

        return response()->json($item, 200);
    }

    public function delete($id){
        if(AuthController::internal_getLoggedInUser()['id'] != $id){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        $item = User::find($id);

        try{
            $item->delete();
        } catch (PDOException $e){
            $item['is_active'] = 0;
            $item->update();
            return response()->json(array('message' => 'Не може да се избрише поради завистности во други релации! Затоа корисникот е деактивиран!'), 200);
        }

        return response(array('message' => 'Успешно избришан запис!'), 200);
    }

    public function getProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required'
        ], \ValidatorMessages::messages);

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }

        $item = User::where('username', '=', $request['username'])->first();

        if($item == null){
            return response()->json(array('message' => 'Ресурсот не постои!'), 404);
        }

        if(AuthController::internal_getLoggedInUser()->role->role != $item->role->role)
            if($item->role->role == 'ROLE_ADMIN')
                abort(401, 'Немате пристап до овој дел од веб апликацијата!');

        unset($item['password'], $item['created_at'], $item['is_active']);
        $item['posts'] = $this->internal_getPostsForUser($item['id']);
        $item['followers'] = $this->internal_getFollowersForUser($item['id']);
        $item['following'] = $this->internal_getFollowingsForUser($item['id']);
        $item['rating'] = $this->internal_getRatingForUser($item['posts']);

        return response()->json($item, 200);
    }

    public function timeline(){
        $listOfUsersThatThisLoggedInUserFollows = $this->internal_getFollowingsForUser(AuthController::internal_getLoggedInUser()->id);
        $listOfFollowingIDs = array();

        foreach ($listOfUsersThatThisLoggedInUserFollows as $item) {
            array_push($listOfFollowingIDs, $item['following_id']);
        }

        if(count($listOfUsersThatThisLoggedInUserFollows) == 0)
            return response()->json($listOfUsersThatThisLoggedInUserFollows, 200);

        $finalListOfPosts = Post::whereIn('seller_id', $listOfFollowingIDs)->where('quantity_left', '>', 0)->with('category')->orderBy('datetime_posted', 'DESC')->get();
        $listOfFavouritePosts = FavouriteController::internal_getAllOnlyPostIDs();
        foreach ($finalListOfPosts as $post){
            $post['seller_username'] = User::find($post['seller_id'])->username;
            if(in_array($post['id'], $listOfFavouritePosts))
                $post['is_favourite'] = true;
            else
                $post['is_favourite'] = false;
        }

        return response()->json($finalListOfPosts, 200);
    }

    /* --------- INTERNAL FUNCTIONS --------- */
    private function internal_populateUser($id, $params){
        $item = User::find($id);
        $keys = array_keys($params);
        $forbidden = array('id', 'role_id', 'created_at', 'updated_at');
        $errors = array();

        foreach($keys as $key){
            if($item[$key]==null || in_array($key, $forbidden))
                continue;

            if($key=='password' && in_array('newPass', $keys) && in_array('repeatPass', $keys)) {
                if(password_verify($params[$key], $item['password'])){
                    // Точно внесен стар пасворд
                    if($params['newPass'] == $params['repeatPass']){
                        // Новите две лозинки се исти
                        $item['password'] = Hash::make($params['newPass']);
                        continue;
                    } else{
                        // Новите две лозинки се различни
                        $errors += ['repeatPass' => 'Внесените нови лозинки не се совпаѓаат!'];
                    }
                } else {
                    // Погрешно внесен стар пасворд
                    $errors += ['password' => 'Внесената лозинка не е точна!'];
                }

                continue;
            }

            if($key == 'email'){
                // Further validation
                if(User::where('email', '=', mb_strtolower($params['email']))->first() != null){
                    // Постои оваа email адреса
                    $errors += [
                        'email' => 'Email адресата постои!'
                    ];
                    continue;
                }
            }

            if($key == 'username'){
                // Further validation
                if(User::where('username', '=', mb_strtolower($params['username']))->first() != null){
                    // Постои овој username
                    $errors += [
                        'username' => 'Корисничкото име постои!'
                    ];
                    continue;
                }
            }

            $item[$key] = $params[$key];
        }

        return ['item' => $item, 'errors' => $errors ];
    }
    private function internal_getPostsForUser($id){
        $items = Post::with(['category'])->where('seller_id', '=', $id)->orderBy('datetime_posted', 'DESC')->get();

        return $items;
    }
    private function internal_getFollowersForUser($id){
        $items = Follower::where('following_id', '=', $id)->get();
        return $items;
    }
    private function internal_getFollowingsForUser($id){
        $items = Follower::where('follower_id', '=', $id)->get();
        return $items;
    }
    private function internal_getRatingForUser($posts){
        $totalPurchases = 0;
        $totalSumOfRatings = 0;

        foreach ($posts as $post){
            $allPurchasesForPost = Purchase::where('post_id', '=', $post['id'])->get();
            foreach ($allPurchasesForPost as $purchase){
                if($purchase['rating'] != null){
                    $totalPurchases++;
                    $totalSumOfRatings += $purchase['rating'];
                }
            }
        }

        if($totalPurchases == 0)
            return "/";
        else
            return $totalSumOfRatings / $totalPurchases;
    }
}