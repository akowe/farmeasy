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
          'service_provider' => 'required',
          'amount' => 'required',
          'user_id' => 'required',
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
          $orderRequest->amount = $request->amount;
          $orderRequest->location = $request->location;
          $orderRequest->land_hectare = $request->measurement;
          $orderRequest->service_type =$request->service_type;
          $orderRequest->sp_id =$request->service_provider;
          $orderRequest->status = "accepted";
          $orderRequest->save();
  
          $status = true;
          $message ="Your ".$request->service_type." request is successful";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);               
      }    
  
  }




  //approve  request
    public function approveRequest(Request $request){
      
       // validation
       $validator =Validator ::make($request->all(), [
        'agent_id' => 'required',
        'status' => 'accepted',
        ]);      
        if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
        }else{ 

            $request  = OrderRequest::where('agent_id',$request->agent_id)
            ->update([
            'land_hectare' =>$request->input('meaurement'),
            'status' => "accepted"
            ]);

    
            $status = true;
            $message ="You have successully accepted the order";
            $error = "";
            $data = "";
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code); 
        }
    

   } 

 //fetch all agent by location 
  public function getAgentsByLocation(Request $request){
    $location = $request->location;
    $allProfile= UserProfile::where("location", $location)->get();
    if($allProfile){
        $agents = array();
        foreach($allProfile as $profile){
            $user= User::where("id", $profile->user_id)->first();
            $agents['agent_id'] = $user->id;
            $agents['agent_name'] = $user->name;
        }
        $status = true;
        $message ="";
        $error = "";
        $data = $agents;
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 

    }else{
        $status = false;
        $message ="No agent available for ".$request->location." location";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }


  } 

  // fetch all location 
  public function getAllAgentsLocation(){
    $locations = array();
    $users= User::where("id", "3")->get();
    if($users){
        foreach($users as $user){
         
            $allProfile= UserProfile::where("user_id", $user->id)->first();
            foreach($allProfile as $profile){
  
              $locations['location'] = $profile->location;
          
          }          
      }
         
          $status = true;
          $message ="";
          $error = "";
          $data = $locations;
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{

          $status = false;
          $message ="No location available";
          $error = "";
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }


  }

  // 
  public function allRequestByLocation(Request $request){
    $location = $request->location;
    $all_request = OrderRequest::where("location", $location)->get();
    if($all_request){
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
  
  //add new product
  public function addProduct(Request $request){
    // validation
    $validator =Validator ::make($request->all(), [
      'name' => 'required',
      'product_type' => 'required',
      'quantity' => 'required',
      'price' => 'required',
      'rent_sell' => 'required',
      'description' => 'required',
      'user_id' => 'required'
  

  ]);      
  if($validator->fails()){
  $status = false;
  $message ="";
  $error = $validator->errors()->first();
  $data = "";
  $code = 401;                
  return ResponseBuilder::result($status, $message, $error, $data, $code);   
  }else{
      $product = new ServiceProduct();
      $product->product_name = $request->name;
      $product->product_type = $request->product_type;
      $product->qty = $request->quantity;
      $product->price = $request->price;
      $product->rent_sell = $request->rent_sell;
      $product->description = $request->description;
      $product->user_id = $request->user_id;
      $product->save();

      $status = true;
      $message ="You have successfully created ".$request->name." as a product";
      $error = "";
      $data = "";
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
  } 
}


  //fetch all product
  public function allProducts(){
 
    $all_products  = ServiceProduct::all();

    $status = true;
    $message ="";
    $error = "";
    $data = $all_products;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 


  } 

}