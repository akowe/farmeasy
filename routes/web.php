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

//add some new route 
$router->group(['prefix' => 'api'], function () use ($router) {


    $router->post('otp', ['uses' => 'UserController@getOtp']);

    $router->post('farmer', ['uses' => 'FarmerController@createFarmer']);

    $router->get('all_farm_types', ['uses' => 'FarmerController@allFarmTypes']); 

    // select each Farm / Crop Type

    $router->get('rice_farm', ['uses' => 'FarmerController@RiceFarm']); 

    $router->get('wheat_farm', ['uses' => 'FarmerController@WheatFarm']); 

    $router->get('maize_farm', ['uses' => 'FarmerController@MaizeFarm']);   


    $router->post('service', ['uses' => 'ServiceController@createService']);


    $router->get('all_service_types', ['uses' => 'ServiceController@allServiceTypes']);

    // select each service type
   $router->get('tractor_service', ['uses' => 'ServiceController@TractorService']);

    $router->get('plough_service', ['uses' => 'ServiceController@PloughService']);

    $router->get('harrow_service', ['uses' => 'ServiceController@HarrowService']);

    $router->get('ridger_service', ['uses' => 'ServiceController@RidgerService']);

    $router->get('planter_service', ['uses' => 'ServiceController@PlanterService']);

    $router->get('boom_service', ['uses' => 'ServiceController@BoomService']);

    $router->get('pesticide_service', ['uses' => 'ServiceController@PesticideService']);

    $router->get('fertilizer_service', ['uses' => 'ServiceController@FertilizerService']);

    $router->get('seed_service', ['uses' => 'ServiceController@SeedService']);

    $router->get('extension_service', ['uses' => 'ServiceController@ExtensionService']);

    $router->get('offtaker_service', ['uses' => 'ServiceController@OffTakerService']);

    $router->get('harvester_service', ['uses' => 'ServiceController@HarvesterService']);

    $router->get('treasher_service', ['uses' => 'ServiceController@TreasherService']);

    //end services


    $router->get('logout', ['uses' => 'UserController@logout']);

    $router->get('countries', ['uses' => 'UserController@allCountries']);

    //authenticate login user
    $router->post('authenticate', ['uses' => 'UserController@authenticateUser']);

    $router->post('verify', ['uses' => 'UserController@verifyUser']);

    $router->post('verify_agent', ['uses' => 'UserController@verifyAgent']); 
    
    $router->post('forgot_password', ['uses' => 'UserController@userForgotPassword']);

    $router->post('reset_password', ['uses' => 'UserController@userResetPassword']);

    //request to become an agent
    $router->post('become_agent', ['uses' => 'UserController@BecomeAnAgent']);

});


$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {
    
   //GENERAL
    $router->get('user', ['uses' => 'UserController@user']);

    $router->get('profile', ['uses' => 'UserController@getProfile']);

    $router->put('profile', ['uses' => 'UserController@updateProfile']);

    $router->post('feedback', ['uses' => 'UserController@feedBack']);

    $router->get('feedbacks', ['uses' => 'UserController@getFeedBack']); 

    $router->post('rating', ['uses' => 'UserController@rating']); 

    $router->get('get_rating', ['uses' => 'UserController@getRating']); 


    //FARMER ACCOUNT
    $router->post('farmer_request', ['uses' => 'FarmerController@requestService']);

    $router->post('tractor', ['uses' => 'FarmerController@HireTractor']);

    $router->post('plough', ['uses' => 'FarmerController@HirePlough']);

    $router->post('planter', ['uses' => 'FarmerController@HirePlanter']);

    $router->post('seed', ['uses' => 'FarmerController@HireSeed']);

    $router->post('pesticide', ['uses' => 'FarmerController@HirePesticide']);

    $router->post('fertilizer', ['uses' => 'FarmerController@HireFertilizer']);

    $router->post('harrow', ['uses' => 'FarmerController@HireHarrow']);

    $router->post('harvester', ['uses' => 'FarmerController@HireHarvester']);

    $router->post('ridger', ['uses' => 'FarmerController@HireRidger']);

    $router->post('boom_sprayer', ['uses' => 'FarmerController@HireBoom']);

     $router->post('treasher', ['uses' => 'FarmerController@HireTreasher']);

    $router->post('extension_service', ['uses' => 'FarmerController@HireExtension']);

    $router->post('off_taker', ['uses' => 'FarmerController@HireOfftaker']);



    //end request for service

    $router->get('farm_history', ['uses' => 'FarmerController@FarmerRequestHistory']); 

    $router->get('farmer_notifications', ['uses' => 'NotificationController@getFarmerNotifications']);   

     $router->get('farmer_read_notification', ['uses' => 'NotificationController@getFarmerNotification']); 


     // AGENT ACCOUNT
     $router->post('farmer_request', ['uses' => 'AgentController@requestService']);

     $router->get('get_service_type_by_request', ['uses' => 'AgentController@getServiceTypeByRequest']); 

     $router->put('update_request_with_service_provider/{request_id}', ['uses' => 'AgentController@updateRequestWithServiceProvider']);
     
     $router->put('update_request_measurement/{request_id}', ['uses' => 'AgentController@updateRequestMeasurement']);
 
     $router->get('agent_notifications', ['uses' => 'NotificationController@getAgentNotifications']);   

     $router->get('agent_read_notification', ['uses' => 'NotificationController@getAgentNotification']); 

     //ALL REQUEST IN AGENT LOCATION

    $router->get('all_farmer_request', ['uses' => 'AgentController@allFarmerRequestByLocation']);

    $router->get('all_farmer_plower_request', ['uses' => 'AgentController@allFarmerPlowerRequest']);
    
    $router->get('all_farmer_tractor_request', ['uses' => 'AgentController@allFarmerTractorRequest']);

    $router->get('all_farmer_planter_request', ['uses' => 'AgentController@allFarmerPlanterRequest']);

    $router->get('all_farmer_seed_request', ['uses' => 'AgentController@allFarmerSeedRequest']);
    
    $router->get('all_farmer_pesticide_request', ['uses' => 'AgentController@allFarmerPesticideRequest']);

    $router->get('all_farmer_fertilizer_request', ['uses' => 'AgentController@allFarmerFertilizerRequest']);
    
    $router->get('all_farmer_processor_request', ['uses' => 'AgentController@allFarmerProcessorRequest']);

    $router->get('all_farmer_harvester_request', ['uses' => 'AgentController@allFarmerHarvesterRequest']);

    $router->get('all_farmer_extension_manager_request', ['uses' => 'AgentController@allFarmerExtensionManagerRequest']);
     //

    $router->put('approve_request', ['uses' => 'AgentController@approveRequest']);

    $router->post('agent_request', ['uses' => 'AgentController@requestService']);

    $router->get('agents', ['uses' => 'AgentController@getAgentsByLocation']); 

    $router->post('sell', ['uses' => 'AgentController@forSell']); 

    $router->get('all_for_sell', ['uses' => 'AgentController@allForSell']);

     $router->get('all_agent_transaction', ['uses' => 'AgentController@allAgentPayment']);  
    
    //PAYSTACK API
    $router->post('/pay', ['uses' => 'AgentController@pay']); 

    $router->get('payment/{requset_id}', ['uses' => 'PaymentController@payment']); 

    $router->get('all_payments', ['uses' => 'PaymentController@allPayment']); 



    //SERVICE PROVIDER
    $router->get('tractor_service_provider', ['uses' => 'ServiceController@getServiceProvidersByTractor']);


    $router->get('plower_service_provider', ['uses' => 'ServiceController@getServiceProvidersByPlower']);

    $router->get('planter_service_provider', ['uses' => 'ServiceController@getServiceProvidersByPlanter']);
    
    $router->get('seed_service_provider', ['uses' => 'ServiceController@getServiceProvidersBySeed']);

    $router->get('pesticide_service_provider', ['uses' => 'ServiceController@getServiceProvidersByPesticide']);


    $router->get('fertilizer_service_provider', ['uses' => 'ServiceController@getServiceProvidersByFertilizer']);

    $router->get('processor_service_provider', ['uses' => 'ServiceController@getServiceProvidersByProcessor']);

    $router->get('harvester_service_provider', ['uses' => 'ServiceController@getServiceProvidersByHarvester']);

    $router->get('extension_manager_service_provider', ['uses' => 'ServiceController@getServiceProvidersByExtensionManager']);


    $router->get('service_providers_by_service_type', ['uses' => 'ServiceController@getServiceProvidersByServiceType']);

     $router->post('product', ['uses' => 'ServiceController@addProduct']);

    $router->get('products', ['uses' => 'ServiceController@allProducts']);

    $router->get('all_farmer_agent_request', ['uses' => 'ServiceController@allFarmerAgentRequestByLocation']);

    $router->get('all_products_by_service_provider', ['uses' => 'ServiceController@allProductsByServiceProvider']);  

    $router->get('service_notifications', ['uses' => 'NotificationController@getServiceNotifications']);   

    $router->get('service_read_notification', ['uses' => 'NotificationController@getServiceNotification']); 

    $router->get('get_farm_request', ['uses' => 'ServiceController@getFarmRequest']); 

    $router->get('get_agent_payment', ['uses' => 'ServiceController@getAgentPayment']); 

    $router->put('accept_request', ['uses' => 'ServiceController@acceptRequest']); 

    $router->put('reject_request', ['uses' => 'ServiceController@rejectRequest']); 

    $router->put('start_service', ['uses' => 'ServiceController@startService']); 

    $router->put('end_service', ['uses' => 'ServiceController@endService']); 




    // ADMIN
    $router->get('users', ['uses' => 'UserController@index']);

    $router->post('admin_request', ['uses' => 'AdminController@requestService']);

    $router->post('agent', ['uses' => 'UserController@createAgent']); 

    $router->put('edit_farmer_request', ['uses' => 'AdminController@editFarmerAgent']);

    $router->put('assign_request_to_agent', ['uses' => 'AdminController@assignRequestToAgent']); 
 

    $router->get('prices', ['uses' => 'PriceController@allPrice']);

    $router->get('price', ['uses' => 'PriceController@editPrice']);

    $router->put('update_price', ['uses' => 'PriceController@updatePrice']);

    $router->post('add_service_type', ['uses' => 'SuperAdminController@addServiceType']); 

    $router->get('edit_service_type', ['uses' => 'SuperAdminController@editServiceType']);

    $router->put('update_service_type', ['uses' => 'SuperAdminController@updateServiceType']);

    $router->put('delete_service_type', ['uses' => 'SuperAdminController@deleteServiceType']);

    $router->get('get_price_by_service_type', ['uses' => 'PriceController@getPriceByServiceType']);

   
    

    

    //SUPER ADMIN
    $router->post('admin', ['uses' => 'SuperAdminController@createAdmin']);

    $router->post('agent', ['uses' => 'UserController@createAgent']); 

    $router->get('all_request', ['uses' => 'SuperAdminController@allRequest']);

    $router->delete('delete_order_request', ['uses' => 'SuperAdminController@deleteOrderRequest']);

    $router->delete('user', ['uses' => 'UserController@deleteUser']);


  
});



