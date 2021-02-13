<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App;

class RoleController extends Controller
{
    public function getAll(){
        return response()->json(Role::all());
    }

    /*
     * Request contains:
     *      Required:
     *      @username   string
     */

    static public function internal_get($role){
        $item = Role::where('role', '=', $role)->first();

        return $item['id'];
    }

}