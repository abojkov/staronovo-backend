<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth as SecondJWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public static function internal_getLoggedInUser(){
        return Auth::user();
    }

    public function getLoggedInUser(){
        $item = Auth::user();
        unset($item['password']);
        $item['role'] = $item->role;
        return response()->json($item);
    }

    public function postLogout(Request $request){
        $token = explode(' ', $request->headers->all()['authorization'][0])[1];
        SecondJWTAuth::setToken($token)->invalidate();

        return response()->json(['message' => 'successfull logout', 200]);
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username'    => 'required|max:255',
            'password' => 'required',
        ], \ValidatorMessages::messages);

        try {

            if (! $token = $this->jwt->attempt($request->only('username', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }

        return response()->json(compact('token'));
    }
}