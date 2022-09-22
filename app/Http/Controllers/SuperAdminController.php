<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\User;
use App\Otp;
use App\Role;
use App\OrderRequest;
use Carbon\Carbon;
use Carbon\Profile;
use App\Country;

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
        $name = $request['name'];
        $country = new Country();               
        $user = new User();
        $user->name = $name;// required 
        
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

         //generate random code insert to otp table send otp to user phone
          $reg_code   = random_int(100000, 999999); //random unique 6 figure str_random(6)
          $otp        = new Otp();
          $otp->code  = $reg_code;
          $otp->save();

        $user->country      = $request->country;
        $user->phone       = $request['phone']; 
        $user->reg_code    = $reg_code;
        $user->user_type   =  '2'; // can select from role table
        // $user->farm_type   = $request['farm_type']; //Creating admin do not need this f 
        $user->password    = Hash::make($request['password']);
        $user->status      = 'verified';
        $user->save();
        
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


  // fetch all request 
  public function allRequest(){
 
    $all_orders = OrderRequest::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_orders;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }    

  public function deleteOrderRequest(Request $request,  User $user){
    $request_id = $request->request_id;
    $orderRequest  = OrderRequest::where('id', $request_id)->first();
    if(Gate::allows('destroy', $user)){
      if($orderRequest){
        if($orderRequest->status =="remove"){
          $status = false;
          $message ="This order has already been deleted";
          $error = "";
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
        }else{
          $orderRequest  = OrderRequest::where('id', $request_id)
          ->update([
            'status' =>'remove'
          ]);
          $status = true;
          $message ="You have successfully deleted a request order";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);  
        }

      }else{
        $status = false;
        $message ="Order request not found";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);  
      }

    }else{
      $status = false;
      $message ="Not Authorized to delete an";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    }
   
  }

}