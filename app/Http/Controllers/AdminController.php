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
use App\UserProfile;
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
        'user_id' => 'required',
        'name' => 'required',
        'sp_id' => 'required',
        'phone' => 'required|min:11||numeric',
        'measurement' => 'required|numeric',
        'amount' => 'required|numeric',
        'location' => 'required',
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

                  
    }

}
    //edit farmer request
    public function editFarmerAgent(Request $request, User $user){
      if(Gate::allows('destroy', $user)){
        // validation
        $validator =Validator ::make($request->all(), [
          'request_id' => 'required',
          'phone' => 'required',
          'location'=> 'required',
          'measurement' => 'required',
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
         
          $profile = array(
            'phone' => $request->input('phone'), 
            'measurement' => $request['measurement'],
            'location' => $request['location']
          );

          $orderRequest = OrderRequest::where('id', $request_id)
          ->update($orderRequest);
          $status = true;
          $message ="Request successfully updated";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
        }
  
      }else{
       $status = false;
       $message ="Not Authorized to edit a request";
       $error = "";
       $data = "";
       $code = 401;                
       return ResponseBuilder::result($status, $message, $error, $data, $code);
      }      

  }

    //assign request to agent
    public function assignRequestTogent(Request $request){
      
      // validation
      $validator =Validator ::make($request->all(), [
        'agent_id' => 'required',
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
      $orderRequest = array(
        'agent_id' =>$request->agent_id
      );

      $orderRequest  = OrderRequest::where('id', $request_id)
      ->update($orderRequest);
      $status = true;
      $message ="You have successfully assigned request to an agent";
      $error = "";
      $data = "";
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }
  }


}//class
