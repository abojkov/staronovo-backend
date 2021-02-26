<?php

namespace App\Http\Controllers;

use App;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    public function toggleFollow(Request $request){
        $validator = Validator::make($request->all(), [
            'follower_id' => 'required',
            'following_id' => 'required'
        ], \ValidatorMessages::messages);

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }

        if(AuthController::internal_getLoggedInUser()['id'] != $request['follower_id']){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        if(User::find($request['following_id']) == null){
            // Непостоечки корисник
            return response()->json(array('message' => 'Корисникот не постои!'), 404);
        }

        $item = Follower::where('follower_id', '=', $request['follower_id'])->where('following_id', '=', $request['following_id'])->first();
        if($item == null){
            // Не постои оваа комбинација на следачи, да се додаде
            $newFollowRelation = array(
                'follower_id' => $request['follower_id'],
                'following_id' => $request['following_id'],
                'datetime_follow' => \Carbon\Carbon::now());

            // Insert
            Follower::create($newFollowRelation);
            return response()->json(array('message' => 'Успешно заследување!'), 200);
        } else {
            // Да се направи одследување
            DB::table('followers')->where('follower_id', '=', $request['follower_id'])->where('following_id', '=', $request['following_id'])->delete();
            return response()->json(array('message' => 'Успешно отследување!'), 200);
        }
    }
}