<?php

namespace App\Http\Controllers;

use App;
use App\Models\Favourite;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    public function toggleFavourite(Request $request){
        $user_id = AuthController::internal_getLoggedInUser()['id'];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ], \ValidatorMessages::messages);

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }

        if(Post::find($request['post_id']) == null){
            // Непостоечки пост
            return response()->json(array('message' => 'Објавата не постои!'), 404);
        } else if(Post::find($request['post_id'])['seller_id'] == $user_id){
            // Свој пост
            return response()->json(array('message' => 'Невозможна акција за свој личен пост!'), 403);
        }

        $item = Favourite::where('post_id', '=', $request['post_id'])->first();
        if($item == null){
            // Не постои оваа комбинација на favourite, да се додаде
            $newFavouriteRelation = array(
                'post_id' => $request['post_id'],
                'user_id' => $user_id,
                'datetime_favourite' => \Carbon\Carbon::now());

            // Insert
            Favourite::create($newFavouriteRelation);
            return response()->json(array('message' => 'Успешнo додавање на омилена објава!'), 200);
        } else {
            // Да се направи отстранување на омилена објава
            DB::table('favourites')->where('post_id', '=', $request['post_id'])->where('user_id', '=', $user_id)->delete();
            return response()->json(array('message' => 'Успешнo отстранување на омилена објава!'), 200);
        }
    }

    public function getAll(){
        $myFaves = self::internal_getAllOnlyPostIDs();
        $finalListOfPosts = array();

        foreach($myFaves as $id){
            array_push($finalListOfPosts, Post::with('category')->find($id));
        }

        foreach ($finalListOfPosts as $post){
            $post['seller_username'] = User::find($post['seller_id'])->username;
            if(in_array($post['id'], $myFaves))
                $post['is_favourite'] = true;
            else
                $post['is_favourite'] = false;
        }

        return response()->json($finalListOfPosts);
    }

    /* --------- INTERNAL FUNCTIONS --------- */
    public static function internal_getAll(){
        return Favourite::where('user_id', '=', AuthController::internal_getLoggedInUser()['id'])->get()->toArray();
    }

    public static function internal_getAllOnlyPostIDs(){
        return array_map(function($o) { return $o['post_id']; }, self::internal_getAll());
    }
}