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


    $router->post('otp', ['uses' => 'UserController@getOtp']);

    $router->post('farmer', ['uses' => 'FarmerController@createFarmer']);

    $router->get('all_farm_types', ['uses' => 'FarmerController@allFarmTypes']);   

    $router->post('service', ['uses' => 'ServiceController@createService']);

    $router->get('all_service_types', ['uses' => 'ServiceController@allServiceTypes']);

    $router->put('user', ['uses' => 'UserController@updateUser']);

    $router->delete('user', ['uses' => 'UserController@deleteUser']);

    $router->get('users', ['uses' => 'UserController@index']);

    $router->post('verify', ['uses' => 'UserController@verifyUser']);
    
    $router->get('user', ['uses' => 'UserController@user']);

    $router->get('profile', ['uses' => 'UserController@getProfile']);

    $router->post('profile', ['uses' => 'UserController@updateProfile']);

    $router->post('forgot_password', ['uses' => 'UserController@userForgotPassword']);

    $router->post('reset_password', ['uses' => 'UserController@userResetPassword']);

    //authenticate login user
    $router->post('authenticate', ['uses' => 'UserController@authenticateUser']);

});