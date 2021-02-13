<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App;

class PostController extends Controller
{
    public function get($id){
        $item = Post::find($id);

        if($item == null){
            return response(array('message' => 'Ресурсот не постои!'), 404);
        }

        return response()->json($item, 200);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'quantity_left' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            'city_id' => 'required',
        ], \ValidatorMessages::messages);

        if(!str_contains(substr($request['image'], 0, 50), 'data:image')){
            $validator->errors()->add(
                'image', 'Полето не може да е празно и мора да е слика!'
            );
        } else {
            $sizeOfPictureInKB = strlen(base64_decode($request['image'])) / 1024;
            if($sizeOfPictureInKB > 2000)
                $validator->errors()->add(
                    'image', 'Сликата мора да има максимална големина од 2MB!'
                );
        }

        if($request['category_id'] < 1){
            $validator->errors()->add(
                'category_id', 'Полето не може да е празно!'
            );
        }
        if($request['city_id'] < 1){
            $validator->errors()->add(
                'city_id', 'Полето не може да е празно!'
            );
        }

        $category = Category::find($request['category_id']);
        if($category == null || $category['is_active'] == 0){
            $validator->errors()->add(
                'category_id', 'Невалидна или неактивна категорија!'
            );
        }

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }



        $request['seller_id'] = Auth::user()['id'];
        $request['is_active'] = true;
        $request['datetime_posted'] = \Carbon\Carbon::now();

        $item = Post::create($request->all());

        return response()->json($item, 201);
    }

    public function update($id, Request $request){
        $post = Post::find($id);

        if($post == null){
            return response()->json(array('message' => 'Ресурсот не постои!'), 404);
        }

        if(AuthController::internal_getLoggedInUser()['id'] != $post['seller_id']){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'quantity_left' => 'required'
        ], \ValidatorMessages::messages);

        if($request['price'] != null && $request['price'] <= 0){
            $validator->errors()->add(
                'price', 'Невалдина вредност!'
            );
        }

        if($request['quantity_left'] < 0){
            $validator->errors()->add(
                'quantity_left', 'Невалдина вредност!'
            );
        }

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }

        $post->update($request->all());
        return response()->json($post, 200);
    }

    public function delete($id){
        $loggedInUser = AuthController::internal_getLoggedInUser();
        $item = Post::find($id);

        if($item == null){
            return response(array('message' => 'Ресурсот не постои!'), 404);
        }

        if($item['seller_id'] != $loggedInUser->id){
            return response(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        $item->delete();
        return response(array('message' => 'Успешно избришан запис!'), 200);
    }
}