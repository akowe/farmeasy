<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\FeedBack;
use App\Otp;
use App\OrderRequest;
use Carbon\Carbon;
use App\UserProfile;
use Carbon\Profile;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class AgentController extends Controller
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
        // $profile = UserProfile::where(function($q){
        //   return $q->whereNull("profile_update_at");
        // })->first();
        // if($profile){
        //   $status = false;
        //   $message ="Update your profile";
        //   $error = "";
        //   $data = "";
        //   $code = 401;                
        //   return ResponseBuilder::result($status, $message, $error, $data, $code);   
        // }else{
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
        //}        
          
      }    
  
  }




  //approve  request
    public function approveRequest(Request $request){
      
       // validation
       $validator =Validator ::make($request->all(), [
        'id' => 'required',
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

            $requestResult  = OrderRequest::where('id',$request->id)
            ->update([
             'agent_id' => $request->agent_id,
            'status' => "accepted"
            ]);
            $requestResult = OrderRequest::where('id',$request->id)->first();
            
            $curl = curl_init();

            $user_id = Auth::user()->id;
            $profileResult = UserProfile::where('id',$user_id)->first();
            $email = $profileResult->email;
            $amount = $requestResult->amount; 
            
            // url to go to after payment
            $callback_url = site_url().'/pay/callback.php';  
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode([
                'amount'=>$amount,
                'email'=>$email,
                'callback_url' => $callback_url
              ]),
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
                       
   
            $status = true;
            $message ="Transaction successful";
            $error = "";
            $data = $data = array("status"=>"successful");
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code); 
        }
    

   } 

 
  // all farmer request by location
  public function allFarmerRequestByLocation(Request $request){
    $location = $request->location;
    $all_request = OrderRequest::where("location", $location)->where('status','!=','remove')->get();
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