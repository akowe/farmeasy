<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Otp;
use App\Role;
use App\OrderRequest;
use Carbon\Carbon;
use Carbon\Profile;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class SuperAdminController extends Controller
{

    public function __construct()
    {
      //create superadmin  
      // $user = User::firstOrNew(['name' => 'superadmin', 'phone' => '08188373898']);
      // $user->ip = 'none';
      // $user->name ="superadmin";
      // $user->phone ="08188373898";
      // $user->country      = 'Nigeria';
      // $user->country_code ='+234';
      // $user->user_type   =  '1'; // can select from role table
      // $user->password    = Hash::make('password');
      // $user->status      = 'verified';
      // $user->save();
    }    
    // create admin 
    public function createAdmin(Request $request){
      
        // validation
        $validator =Validator ::make($request->all(), [
         'ip' => 'required',
         'name' => 'required',
         'country' => 'required',
         'phone' => 'required',
         'password' => 'required'
     ]);      
      if($validator->fails()){
       $status = false;
       $message ="";
       $error = $validator->errors()->first();
       $data = "";
       $code = 401;                
       return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }else{
        $country = new Country();               
        $user = new User();
        $user->name         = $request['name']; // required 
        
        $countryCode= $country->get_country_code($request->country); // select from db
        if($countryCode !="false"){
          $user->country_code = $countryCode->country_code;
        }else{
          $status = false;
          $message ="This application is not allowed in ".ucfirst($request->country);
          $error = '';
          $code = 400;     
          return ResponseBuilder::result($status, $message, $error, $code);                 
        }
        $user->country      = $request->country;
        $user->phone       = $request['phone']; 
        $user->reg_code    = $reg_code;
        $user->user_type   =  '2'; // can select from role table
        $user->farm_type   = $request['farm_type']; //select fron db 'service' 
        $user->password    = Hash::make($request['password']);
        $user->status      = 'verified';
    
        $status = true;
        $message ="You have successfull created ".$name." as an Admin";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);               
      }    
  
  } 


    // request for service 
    public function requestService(Request $request){
      
      // validation
      $validator =Validator ::make($request->all(), [

        'service_type' => 'required',
        'amount' => 'required',
        'user_id' => 'required',
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
        $orderRequest = new OrderRequest();
        $orderRequest->user_id = $request->user_id;
        $orderRequest->name = $request->name;
        $orderRequest->phone = $request->phone;
        $orderRequest->amount = $request->amount;
        $orderRequest->location = $request->location;
        $orderRequest->land_hectare = $request->measurement;
        $orderRequest->service_type =$request->service_type;
        $orderRequest->sp_id =$request->service_provider;
        $orderRequest->status = "pending";
        $orderRequest->save();

        $status = true;
        $message ="Your ".$request->service_type." request is successful";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);               
    }    

} 


    // get location by user_id
    public function getLocation(Request $request){
      $user_id = $request->user_id;
      $profile = UserProfile::where("user_id", $user_id)->first();
      if($profile ){
          $location = array("location" => $profile->location);
          $status = true;
          $message ="";
          $error = "";
          $data = $location;
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          $status = false;
          $message ="";
          $error = "";
          $data = "No location found";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);
      }

  
    }  

  // fetch all request order
  public function allOrder(){
 
    $all_orders = OrderRequest::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_orders;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }    




}