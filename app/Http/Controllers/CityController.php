<?php

namespace App\Http\Controllers;

use App;
use App\Models\City;

class CityController extends Controller
{
    /*
     * @return: List of cities
     */
    public function getAll(){
        return response()->json(City::all());
    }
}