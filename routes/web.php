<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'api'], function($router){
    $router->post('auth/login', 'AuthController@postLogin');
    $router->post('users', 'UserController@create');

    $router->group(['prefix' => 'auth', 'middleware' => 'auth'], function ($router){
        $router->post('loggedInUser', 'AuthController@getLoggedInUser');
        $router->post('logout', 'AuthController@postLogout');
    });


    $router->group(['prefix' => 'categories', 'middleware' => 'auth'], function($router){
        $router->get('', 'CategoryController@getAll');
        $router->get('/active', 'CategoryController@getAllActive');
        $router->get('{id}', 'CategoryController@get');
        $router->post('', 'CategoryController@create');
        $router->put('{id}', 'CategoryController@update');
        $router->delete('{id}', 'CategoryController@delete');
    });


    $router->group(['prefix' => 'users', 'middleware' => 'auth'], function($router){
        $router->get('', 'UserController@getAll');
        $router->get('search', 'UserController@get');
        $router->put('{id}', 'UserController@update');
        $router->delete('{id}', 'UserController@delete');
        $router->post('profile', 'UserController@getProfile');
        $router->get('timeline', 'UserController@timeline');
    });

    $router->group(['prefix' => 'follow', 'middleware' => 'auth'], function($router){
        $router->post('toggle', 'FollowController@toggleFollow');
    });

    $router->group(['prefix' => 'favourites', 'middleware' => 'auth'], function($router){
        $router->post('toggle', 'FacouriteController@toggleFavourite');
    });

    $router->group(['prefix' => 'posts', 'middleware' => 'auth'], function($router){
        $router->post('', 'PostController@create');
        $router->put('{id}', 'PostController@update');
        $router->get('{id}', 'PostController@get');
        $router->delete('{id}', 'PostController@delete');
    });

    $router->group(['prefix' => 'purchases', 'middleware' => 'auth'], function($router){
        $router->get('', 'PurchaseController@get');
        $router->post('', 'PurchaseController@create');
        $router->delete('{id}', 'PurchaseController@delete');
        $router->get('orders', 'PurchaseController@getOrders');
        $router->post('/deliver/{id}', 'PurchaseController@deliver');
        $router->post('/confirm/{id}', 'PurchaseController@confirm');
        $router->post('/rate/{id}', 'PurchaseController@rate');
    });


    $router->get('cities', 'CityController@getAll');

});
