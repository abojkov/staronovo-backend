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

class UserController extends Controller
{
    public function test(){
        $item = User::find(1);
        return response()->json($item->role);
    }

    /*
     * @return: List of users
     */
    public function getAll(){
        return $this->jsonReponse(User::all());
    }

    /*
     * Request contains:
     *  Required:
     *      @username   string
     *
     * @return: User (searched bu username)
     */
    public function get(Request $request){
        // Validation
        $this->validate($request, [
            'username' => 'required'
        ], \ValidatorMessages::messages);

        // ID за админ е 1
        $item = User::where('username', 'LIKE', '%'.$request['username'].'%')->where('role_id', '!=', 1)->get();

        return response()->json($item);
    }

    /*
     * Request contains:
     *  Required:
     *      @username   string
     *      @email      string
     *      @password   string
     *      @repeatPass string
     *      @name       string
     *      @surname    string
     *
     * @return: User (newly created)
     */
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

    /*
     * Request contains:
     *  Required:
     *      none
     *  Optional:
     *      @email      string
     *      @password   string
     *      @newPass    string
     *      @repeatPass string
     *      @name       string
     *      @surname    string
     *      @is_active  boolean
     *  Forbidden:
     *      @username   The username must not be changed
     *
     * @return: User (updated)
     */
    public function update($id, Request $request){
        $updated = $this->internal_populateUser($id, $request->all());
        $errors = $updated['errors'];
        $updated = $updated['item']->getAttributes();

        if(sizeof($errors)>0){
            // Има грешки
            return response()->json($errors, 422);
        }

        // Insert
        $item = User::findOrFail($id);
        $item->update($updated);
        return response()->json($item, 200);
    }

    /*
     * @return: string (Global message)
     */
    public function delete($id){
        $item = User::find($id);

        if($item == null){
            return response(array('global_message' => 'Ресурсот не постои!'), 404);
        }

        $item->delete();
        return response(array('global_message' => 'Успешно избришан запис!'), 200);
    }

    /*
     *
     */
    public function getProfile(Request $request){
        $item = User::where('username', '=', $request['username'])->first();

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
        foreach ($finalListOfPosts as $post){
            $post['seller_username'] = User::find($post['seller_id'])->username;
        }

        return response()->json($finalListOfPosts, 200);
    }

    /* --------- INTERNAL FUNCTIONS --------- */
    private function internal_populateUser($id, $params){
        $item = $this->internal_getById($id);
        $keys = array_keys($params);
        $forbidden = array('username');
        $errors = array();

        foreach($keys as $key){
            if($item[$key]==null || in_array($item[$key], $forbidden))
                continue;

            if($key=='password' && $params['newPass']!=null && $params['repeatPass']!=null) {
                if($params[$key] == $item['password']){
                    // Точно внесен стар пасворд
                    if($params['newPass'] == $params['repeatPass']){
                        // Новите две лозинки се исти
                        $item[$key] = $params['newPass'];
                    } else {
                        // Новите две лозинки се различни
                        $errors += ['repeatPass' => 'Внесените нови лозинки не се совпаѓаат!'];
                    }
                } else {
                    // Погрешно внесен стар пасворд
                    $errors += ['password' => 'Внесетана лозинка не е точна!'];
                }

                continue;
            }

            // Further validation
            if(User::where('email', '=', mb_strtolower($params['email']))->first() != null){
                // Постои оваа email адреса
                $errors += [
                    'email' => 'Email адресата постои!'
                ];
            }

            $item[$key] = $params[$key];
        }

        return ['item' => $item, 'errors' => $errors ];
    }
    private function internal_getById($id){
        return User::find($id);
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