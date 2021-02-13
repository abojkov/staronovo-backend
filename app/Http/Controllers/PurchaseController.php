<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App;

class PurchaseController extends Controller
{
    public function get(){
        $loggedInUserID = Auth::user()['id'];

        $purchases = Purchase::where('buyer_id', '=', $loggedInUserID)->orderBy('datetime_purchased', 'DESC')->get();

        foreach($purchases as $purchase){
            $purchase['post'] = $purchase->post;
            $purchase['post']['seller_username'] = User::find($purchase['post']['seller_id'])['username'];
        }

        return response()->json($purchases, 200);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'quantity' => 'required'
        ], \ValidatorMessages::messages);

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }

        $post = Post::find($request['post_id']);

        if($post == null){
            return response()->json(array('message' => 'Ресурсот не постои!'), 404);
        }

        if($request['quantity'] > $post['quantity_left']){
            return response()->json(array('message' => 'Нема доволно залиха!'), 403);
        }

        $request['buyer_id'] = Auth::user()['id'];

        if($post['seller_id'] == $request['buyer_id']){
            return response()->json(array('message' => 'Корисникот не може да купи свој производ!'), 403);
        }

        $request['total_price'] = $post['price'] * $request['quantity'];
        $request['status_id'] = 1;
        $request['nth_rating'] = 0;
        $request['datetime_purchased'] = \Carbon\Carbon::now();

        $item = Purchase::create($request->all());

        $post['quantity_left'] -= $item['quantity'];
        $post->update();

        return response()->json($item, 201);
    }

    public function delete($id){
        $purchase = Purchase::find($id);
        $loggedInUserID = Auth::user()['id'];

        if($purchase['buyer_id'] == null){
            return response(array('message' => 'Ресурсот не постои!'), 404);
        }

        if($purchase['buyer_id'] != $loggedInUserID){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        if($purchase['datetime_delivered'] != null){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        $post = Post::find($purchase['post_id']);
        $post['quantity_left'] += $purchase['quantity'];
        $post->update();

        $purchase->delete();

        return response(array('message' => 'Успешно избришан запис!'), 200);
    }

    public function getOrders(){
        $loggedInUserID = Auth::user()['id'];

        $orders = Purchase::with('post')->whereHas('post', function(Builder $query) use ($loggedInUserID){
            $query->where('seller_id', '=', $loggedInUserID);
        })->orderBy('datetime_purchased', 'DESC')->get();

        foreach ($orders as $order) {
            $order['buyer_username'] = User::find($order['buyer_id'])['username'];
        }

        return response()->json($orders, 200);
    }

    public function deliver($id){
        $purchase = Purchase::with('post')->get()->find($id);

        if($purchase == null){
            return response(array('message' => 'Ресурсот не постои!'), 404);
        }

        if(AuthController::internal_getLoggedInUser()['id'] != $purchase->post['seller_id']){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        if($purchase['datetime_delivered'] != null){
            return response()->json(array('message' => 'Пратката е веќе пратена!'), 401);
        }

        $purchase['datetime_delivered'] = Carbon::now();

        $purchase->update();

        $purchase['buyer_username'] = User::find($purchase['buyer_id'])['username'];
        return response()->json($purchase, 200);
    }

    public function confirm($id){
        $purchase = Purchase::with('post')->get()->find($id);

        if($purchase == null){
            return response(array('message' => 'Ресурсот не постои!'), 404);
        }

        if(AuthController::internal_getLoggedInUser()['id'] != $purchase['buyer_id']){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        if($purchase['datetime_confirmation'] != null){
            return response()->json(array('message' => 'Пратката е веќе примена!'), 401);
        }

        $purchase['datetime_confirmation'] = Carbon::now();

        $purchase->update();

        $purchase['buyer_username'] = User::find($purchase['buyer_id'])['username'];
        return response()->json($purchase, 200);
    }

    public function rate(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'rating' => 'required',
            'comment' => 'required'
        ], \ValidatorMessages::messages);

        if(sizeof($validator->errors()) > 0){
            return response()->json($validator->errors(), 422);
        }

        $purchase = Purchase::with('post')->get()->find($id);

        if($purchase == null){
            return response(array('message' => 'Ресурсот не постои!'), 404);
        }

        if(AuthController::internal_getLoggedInUser()['id'] != $purchase['buyer_id']){
            return response()->json(array('message' => 'Немате пристап до овој дел од веб апликацијата!'), 401);
        }

        if($purchase['nth_rating'] > 1){
            return response()->json(array('message' => 'Имате можност да дадете најмногу два рејтинга!'), 401);
        }

        $purchase['rating'] = $request['rating'];
        $purchase['comment'] = $request['comment'];
        $purchase['datetime_rating'] = Carbon::now();
        $purchase['nth_rating'] = $purchase['nth_rating']+1;

        $purchase->update();

        $purchase['buyer_username'] = User::find($purchase['buyer_id'])['username'];
        return response()->json($purchase, 200);
    }


}