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
use App\Country;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class AdminController extends Controller
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

      // $status = false;
      // $message ="User already exist";
      // $error = "";
      // $data = "";
      // $code = 401;                
      // return ResponseBuilder::result($status, $message, $error, $data, $code);
    }


    //admin request for service 
    public function requestService(Request $request){
      
      // validation
      $validator =Validator ::make($request->all(), [

        'service_type' => 'required',
        'amount' => 'required',
        'user_id' => 'required',
        'name' => 'required',
        'service_provider' => 'required',
        'phone' => 'required',
        'location' => 'required',
        'agent_id' => 'required'
      

   ]);      
    if($validator->fails()){
     $status = false;
     $message ="";
     $error = $validator->errors()->first();
     $data = "";
     $code = 401;                
     return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }else{
     
      $profile = UserProfile::where(function($q){
        return $q->whereNull("email")->orWhereNull("business_name")->orWhereNull("address")
        ->orWhereNull("location")->orWhereNull("bank_name")->orWhereNull("account_name")
        ->orWhereNull("account_number")->orWhereNull("profile_update_at");
      })->first();
      if($profile){
        $orderRequest = new OrderRequest();
        $orderRequest->user_id = $request->user_id;
        $orderRequest->name = $request->name;
        $orderRequest->phone = $request->phone;
        $orderRequest->amount = $request->amount;
        $orderRequest->location = $request->location;
        $orderRequest->land_hectare = $request->measurement;
        $orderRequest->service_type =$request->service_type;// this should be select fromdropdown
        $orderRequest->sp_id =$request->sp_id; //this should be selest from dropdown
        $orderRequest->status = "pending";
        $orderRequest->save();
        $status = true;
        $message =Ucwords($request->name)." your ".$request->service_type." request is successful";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);            

      }else{
        $status = false;
        $message ="Update your profile";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);          
      }               
    }

}

}//class
