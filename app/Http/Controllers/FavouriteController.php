<?php

namespace App\Http\Controllers;

use App;
use App\Models\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    public function toggleFavourite(Request $request){
        return response()->json(array('message' => 'Not yet implemented'), 404);
    }
}