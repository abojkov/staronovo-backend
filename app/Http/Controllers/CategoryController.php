<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App;

class CategoryController extends Controller
{
    private $allowedUsers = ['ROLE_ADMIN'];

    public function getAllActive(){
        return response()->json(Category::where('is_active', '=', 1)->get(), 200);
    }

    public function getAll(){
        $this->checkRoles($this->allowedUsers);

        return response()->json(Category::all());
    }

    public function get($id){
        $this->checkRoles($this->allowedUsers);

        $item = Category::find($id);

        if($item == null)
            dd("NE POSTOI");

        return response()->json($item);
    }

    /*
     * Request contains:
     *      Required:
     *      @name       string
     *      Omitted:
     *      @is_active  boolean
     *      @id         unsigned int
     */
    public function create(Request $request){
        $this->checkRoles($this->allowedUsers);

        // Validation
        $this->validate($request, [
            'name' => 'required'
        ], \ValidatorMessages::messages);
        if(Category::where('name', '=', mb_strtolower($request['name']))->first() != null){
            // Exists with same name
            $error = [
                'error' => 'Постои категорија со ова име!'
            ];
            return response()->json($error, 409);
        }

        // Default population
        $request['is_active'] = true;

        // Insert
        $item = Category::create($request->all());

        return response()->json($item, 201);
    }

    /*
     * Request contains:
     *  Required:
     *      @name       string
     *      @is_active  boolean
     *
     * @
     */
    public function update($id, Request $request){
        $this->checkRoles($this->allowedUsers);

        // Validation
        $this->validate($request, [
            'name' => 'required',
            'is_active' => 'required'
        ]);

        // Insert
        $item = Category::findOrFail($id);
        $item->update($request->all());
        return response()->json($item, 200);
    }

    public function delete($id){
        $this->checkRoles($this->allowedUsers);

        $item = Category::find($id);

        if($item == null){
            return response(array('global_message' => 'Ресурсот не постои!'), 404);
        }

        $item->delete();
        return response(array('global_message' => 'Успешно избришан запис!'), 200);
    }

    private function checkRoles($roles){
        foreach ($roles as $role){
            $loggedInUser = AuthController::internal_getLoggedInUser();

            if(!$loggedInUser->hasRole($role))
                abort(401, 'Немате пристап до овој дел од веб апликацијата!');
        }
    }
}