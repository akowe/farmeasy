<?php

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {

<<<<<<< HEAD
    //$router->post('otp', ['uses' => 'UserController@getOtp']);
=======
    $router->post('otp', ['uses' => 'UserController@getOtp']);

>>>>>>> f0d404eef2f8133cac9416c90cc9910e52c569d3
    $router->post('user', ['uses' => 'UserController@createUser']);

    $router->put('user/{id}', ['uses' => 'UserController@updateUser']);

    $router->delete('user/{id}', ['uses' => 'UserController@deleteUser']);

    $router->get('user', ['uses' => 'UserController@index']);

    $router->get('profile/{id}', ['uses' => 'UserController@getProfile']);

    $router->post('profile', ['uses' => 'UserController@updateProfile']);

    $router->post('forgot_password', ['uses' => 'UserController@userForgotPassword']);

    $router->post('reset_password', ['uses' => 'UserController@userResetPassword']);

    $router->get('all_farm_types', ['uses' => 'UserController@allFarmTypes']);

    $router->get('all_service_types', ['uses' => 'UserController@allServiceTypes']);

    //authenticate login user
    $router->post('authenticate', ['uses' => 'UserController@authenticateUser']);

});