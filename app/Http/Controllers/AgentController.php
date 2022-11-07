<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\VerifyCsrfToken;
use App\User;
use App\FeedBack;
use App\Otp;
use App\OrderRequest;
use Carbon\Carbon;
use App\UserProfile;
use Carbon\Profile;
use App\ServiceType;
use App\Payment;
use App\BecomeAgent;
use App\Price;
use App\AgentNotification;
use App\FarmerNotification;
use App\ServiceNotification;

use App\Rice_farm_type;
use App\Wheat_farm_type;
use App\Maize_farm_type;

use App\Boom_sprayer_service;
use App\Extension_service;
use App\Fertilizer_service;
use App\Harrow_service;
use App\Harvester_service;
use App\Off_taker_service;
use App\Pesticide_herbicide_service;
use App\Planter_service;
use App\Plough_service;
use App\Ridger_service;
use App\Seeds_service;
use App\Tractor_service;
use App\Treasher;


use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class AgentController extends Controller
{

    public function __construct()
    {
     
    }   

    // order new  service 
    public function requestService(Request $request){
      
        // validation
        $validator =Validator ::make($request->all(), [
          'service_type' => 'required',
          'sp_id' => 'required',
          'name' => 'required',
          'phone' => 'required',
          'amount' => 'required',
          'location' => 'required',
          'measurement' => 'required'
        
  
     ]);      
      if($validator->fails()){
       $status = false;
       $message ="";
       $error = $validator->errors()->first();
       $data = "";
       $code = 401;                
       return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }else{
       
          $amount = $request->measurement * $request->amount;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id = Auth::user()->id;
          $orderRequest->amount = $request->amount;
          $orderRequest->name = $request->name;
          $orderRequest->phone = $request->phone;
          $orderRequest->location = $request->location;
          $orderRequest->land_hectare = $request->measurement;
          $orderRequest->service_type =$request->service_type;
          $orderRequest->sp_id =$request->sp_id;
          $orderRequest->status = "accepted";
          $orderRequest->save();
           
          $status = true;
          $message =Ucwords($request->name)." your request for " .$request->service_type. " is successful";
          $error = "";
          $data = array("status"=>"payment");
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);     
         
          
      }    
  
  }

    //farmer request for tractor
    public function requestTractor(Request $request){
      
      // validation
      $validator =Validator ::make($request->all(), [

        'service_type' => 'required',
        'amount' => 'required',
        'sp_id' => 'required',
        'name' => 'required',
        'phone' => 'required',
        'location' => 'required',
        'measurement' => 'required'
      

   ]);      
    if($validator->fails()){
     $status = false;
     $message ="";
     $error = $validator->errors()->first();
     $data = "";
     $code = 401;                
     return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }else{
     
          $amount = $request->measurement * $request->amount;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id = Auth::user()->id;
          $orderRequest->name = $request->name;
          $orderRequest->phone = $request->phone;
          $orderRequest->amount = $amount;
          $orderRequest->land_hectare = $request->measurement;
          $orderRequest->location = $request->location;
          $orderRequest->service_type =$request->service_type;// this should be select fromdropdown
          $orderRequest->sp_id =$request->sp_id; //this should be selest from dropdown
          $orderRequest->status = "pending";
          $orderRequest->save();
          $status = true;
          $message =Ucwords($request->name)." your request for ".$request->service_type." is successful";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);            
             
    }    

}

  //update request with service provider id. pass the request id in the route
  public function updateRequestWithServiceProvider($request_id, Request $request){

     // validation
      $validator =Validator ::make($request->all(), [

        'sp_id' => 'required',
        'price' => 'required'

   ]);      
    if($validator->fails()){
     $status = false;
     $message ="";
     $error = $validator->errors()->first();
     $data = "";
     $code = 401;                
     return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
     else{

      //get id of the request to update
    $requestResult  = OrderRequest::where('id',$request_id)->first();
    if($requestResult){
      $service_type = $requestResult->service_type;
      $farmer_id = $requestResult->user_id;
      
      //$priceResult = Price::where('service_type',$service_type)->first();

      // get service provider id
       //$user = User::where('service_type', $service_type)->first();
      
     
       $requestResult  = OrderRequest::where('id',$request_id)
      ->update([

        'sp_id' => $request->sp_id,
        'hectare_rate' => $request->price,
        'agent_id' => Auth::user()->id,
        'status' =>'approved'

      ]);

      //SEND NOTIFICATION TO THE FARMER 
      // OR WHOEVER CREATED THE REQUEST
      $notification= new FarmerNotification();
      $notification->request_id   = $request_id;
      $notification->farmer_id      = $farmer_id;
      $notification->type         = $service_type;
      $notification->description  =  "Your " .strtolower($service_type)." request has been approved";
      $notification->notice_status = "delivered"; 
      $notification->save(); 


      //SEND NOTIFICATION TO SERVICE PROVIDER
      $notification= new ServiceNotification();
      $notification->request_id   = $request_id;
      $notification->sp_id        = $request->sp_id;
      $notification->type         = $service_type;
      $notification->description  =  "You have a new " .strtolower($service_type)." request";
      $notification->notice_status = "delivered"; 
      $notification->save();     
  
  if ($notification)  
     {
      $status = true;
      $message ="Service provider and price successfully updated";
      $error = "";
      $data ="";
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
     } 
     else{
      $status = false;
      $message ="No Service Provider is not found";
      $error = "";
      $data ="";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }   
    }else{
      $status = false;
      $message ="Request is not found";
      $error = "";
      $data ="";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }

} 

}


  //update request measurement
  public function updateRequestMeasurement($request_id, Request $request){
           // validation
      $validator =Validator ::make($request->all(), [

        'farm_size' => 'required'

   ]);      
    if($validator->fails()){
     $status = false;
     $message ="";
     $error = $validator->errors()->first();
     $data = "";
     $code = 401;                
     return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
     else{

    $requestResult  = OrderRequest::where('id',$request_id)->first();
    if($requestResult){

      $measurement = $request->input('farm_size');

      $requestResult  = OrderRequest::where('id',$request_id)
      ->update([

        'farm_size' => $measurement,
        'agent_id' => Auth::user()->id,
        'status' =>'approved and measured'

      ]);
  
      $status = true;
      $message ="Measurement successfully updated";
      $error = "";
      $data =$measurement;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);       
    }else{
      $status = false;
      $message ="Request is not found";
      $error = "";
      $data ="";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }

  }
}

  //approve  request
    public function approveRequest(Request $request){
      
       // validation
       $validator =Validator ::make($request->all(), [
        'request_id' => 'required'
        ]);      
        if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
        }else{ 

            $requestResult  = OrderRequest::where('id',$request->request_id)
            ->update([
             'agent_id' => Auth::user()->id,
            'status' => "approved and measured",
            'pay_status' => "Paid"
            ]);
            $status = true;
            $message ="Request successfully accepted";
            $error = "";
            $data = $data = array("status"=>"payment");
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code); 
        }
    

   } 

 
  // all farmer request by location
  public function allFarmerRequestByLocation(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;

    $all_request = OrderRequest::where("location", $location)->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          // $user_id = $main_request->user_id;
          // $user = User::where("id", $user_id)->first();
          // if($user->user_type =="4" && $user->user_type =="3" ){
          //   $all_request = OrderRequest::where("user_id", $user->id)->get();
          //   $all_farmer_request = $all_request;
          // }

            $profile = UserProfile::where(['user_id' => $user_id])->first();

            $location = $profile->location;
        
         
            $all_request = OrderRequest::where("location", $location)->get();
            $all_farmer_request = $all_request;
          


      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  

  // all agents by location
  public function getAgentsByLocation(Request $request){
     $location = $request->location;
    $profiles = UserProfile::where(['location' => $location])->get();




    if($profiles){
      $agents = array();
      foreach($profiles as $profile){
        $user = User::where(['id' => $profile->user_id])->first();
        $agents[] = ["agent_id" => $user->id,"name" => $user->name];
      }
      $status = true;
      $message ="";
      $error = "";
      $data = $agents;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No agents is found";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  


  // all farmer tractor Request in his location
  public function allFarmerTractorRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Tractor service type from table
    $tractor = ServiceType::where('id', '1')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $tractor)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  


    // all farmer plower Request in his location
  public function allFarmerPlowerRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Plower service type from table
    $plower = ServiceType::where('id', '2')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $plower)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



      // all farmer Planter Request in his location
  public function allFarmerPlanterRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Planter service type from table
    $planter = ServiceType::where('id', '3')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $planter)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



   // all farmer Seed Request in his location
  public function allFarmerSeedRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Seed service type from table
    $Seed = ServiceType::where('id', '4')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $Seed)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



 // all farmer Pesticide Request in his location
  public function allFarmerPesticideRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Pesticide service type from table
    $Pesticide = ServiceType::where('id', '5')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $Pesticide)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  




  // all farmer Fertilizer Request in his location
  public function allFarmerFertilizerRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Fertilizer service type from table
    $Fertilizer = ServiceType::where('id', '6')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $Fertilizer)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  




  // all farmer Harrow Request in his location
  public function allFarmerHarrowRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Harrow service type from table
    $Processor = ServiceType::where('id', '7')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $Processor)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



   // all farmer Harvester Request in his location
  public function allFarmerHarvesterRequest(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Harvester service type from table
    $harvester = ServiceType::where('id', '8')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $harvester)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  


  // all farmer Extention manager Request in his location
  public function allFarmerRidger(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get extension manager service type from table
    $farm_manager = ServiceType::where('id', '9')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $farm_manager)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



  // all farmer Boom Sprayer  Request in his location
  public function allFarmerBoom(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Boom Sprayer service type from table
    $farm_manager = ServiceType::where('id', '10')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $farm_manager)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



  // all farmer Boom Sprayer  Request in his location
  public function allFarmerExtension(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Boom Sprayer service type from table
    $farm_manager = ServiceType::where('id', '11')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $farm_manager)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



    // all farmer Boom Sprayer  Request in his location
  public function allFarmerOfftaker(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Boom Sprayer service type from table
    $farm_manager = ServiceType::where('id', '12')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $farm_manager)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  



    // all farmer Boom Sprayer  Request in his location
  public function allFarmerTreasher(){
    $user_id = Auth::user()->id;
    $profile = UserProfile::where(['user_id' => $user_id])->first();

    $location = $profile->location;
      
      //get Boom Sprayer service type from table
    $farm_manager = ServiceType::where('id', '12')->first()->service;

    $all_request = OrderRequest::where("location", $location)
                    ->where('service_type', $farm_manager)
                    ->where('status','!=','remove')->get();
    $all_farmer_request =array();
    if($all_request){
      foreach($all_request as $main_request){
          $user_id = $main_request->user_id;
          $user = User::where("id", $user_id)->first();
          if($user->user_type =="4" && $user->user_type =="3" ){
            $all_request = OrderRequest::where("user_id", $user->id)->get();
            $all_farmer_request = $all_request;
          }

      }
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "No request currently available";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  




 public function Pay(Request $request){
      
       // validation
       $validator =Validator ::make($request->all(), [
        'request_id' => 'required'
        ]);      
        if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
        }else{ 

           $request_id = $request->request_id;

            $requestResult = OrderRequest::where('id',$request_id)->first();

            // amount = rate x farm_size
           $amount =  $requestResult->hectare_rate * $requestResult->farm_size;
           if(!$amount){
            $status = false;
            $message ="";
            $error = "";
            $data = "Kindly  measure your farm size before making payment";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code); 
           }
            
            $curl = curl_init();

            $user_id = Auth::user()->id;
            $phone = Auth::user()->phone;
            $profileResult = UserProfile::where('user_id',$user_id)->first();
            $email = $profileResult->email;
            // $ref = random_int(100000, 999999);

            //UPDATE THE REFERENCE CODE TO REQUEST TABLE
            // $post_ref = OrderRequest::where('id',$request_id)
            // ->update([
            //  'reference' => $ref
            // ]);

              // url to go to after payment
            $callback_url = 'http://localhost:8000/api/payment';  

            $data = array(
              'callback_url' => $callback_url,
             
              'email'=>$email,
              "amount" => $amount,
              'metadata' =>array('phone' => $phone)

            );

            //$post_data = json_encode($data);
            
              curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($data),
              CURLOPT_HTTPHEADER => [
                "authorization: Bearer sk_test_8fabc18c29f908e5b7540b54d38a4b097250c39b", //replace this with your own test key
                "content-type: application/json",
                "cache-control: no-cache"
              ],
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            if($err){
              // there was an error contacting the Paystack API
              die('Curl returned error: ' . $err);
            }
            
            $tranx = json_decode($response, true);
            
            if(!$tranx['status']){
              // there was an error from the API
              print_r('API returned error: ' . $tranx['message']);
            }
                       
            if($tranx){
             $requestResult  = OrderRequest::where('id',$request_id)
            
            ->update([
             'agent_id' => Auth::user()->id,
            'pay_status' => "Payment pending"
            ]);

            $status = true;
            $message ="Transaction successful";
            $error = "";
            $data = $data;
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code); 
          }
        }
    

   } 


   public function getAgents(){
    //get all agents 
   $user_agents = User::Join('profile','profile.user_id', '=', 'users.id')
                  ->where('users.user_type', '3')
                  ->get(['users.*', 'profile.*']);

    if (!$user_agents ){
      $status = false;
      $message ="No Agent currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $user_agents;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }  
}  





  public function becomeAgent(){
    //get all request to be an  agent from mobile app 
    $user_agents = BecomeAgent::orderByDesc('created_at')->get();

    if (!$user_agents ){
      $status = false;
      $message ="No request to ba agent currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $user_agents;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }  
}   




public function allAgentPayment (Request $request){
  $user_id = Auth::user()->id;
  $agent_phone = Auth::user()->phone;

  // fecth all payment transaction for the log in use
  
$trans = Payment::Join('request', 'request.id', '=', 'payment.request_id')
                ->leftjoin('users', 'users.id', '=', 'request.agent_id') 
                ->where('users.id', $user_id)
                ->get(['request.name', 'payment.amount', 'request.pay_status', 'request.service_type', 'request.farm_size']);

  if(!$trans)
  {
    $status = false;
    $message ="No existing transaction";
    $error = "";
    $data = "";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);
}
else{
  $status = true;
    $message ="success";
    $error = "";
    $data = $trans;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);
}

}

  
  //for sell
  public function forSell(Request $request){
    // validation
    $validator =Validator ::make($request->all(), [
      'name' => 'required',
      'crop_type' => 'required',
      'quantity' => 'required',
      'amount' => 'required|numeric'
    ]);      
    if($validator->fails()){
    $status = false;
    $message ="";
    $error = $validator->errors()->first();
    $data = "";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }else{
      // wht table should i give sell
      // $sell =? ;
      
        //$sell->save();

        $status = true;
        $message ="You have successfully created ".$request->name." as a product";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
    
    } 
  }  

}