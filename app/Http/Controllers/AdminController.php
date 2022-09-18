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

    public function createAgent(Request $request){

        // validation
     $validator =Validator::make($request->all(), [
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
       $code = 400;                
       return ResponseBuilder::result($status, $message, $error, $data, $code);   
       }     
 
       //generate random code insert to otp table send otp to user phone
       $reg_code   = random_int(100000, 999999); //random unique 6 figure str_random(6)
       $otp            = new Otp();
       $otp->code      = $reg_code;
 
       if($otp->save()){
           //send otp as sms to user phone here 
         
           $user_type = 'admin';
           // $role = new Role();
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
           $user->user_type   =  '3'; // can select from role table
           $user->password    = Hash::make($request['password']);
           $user->status      = 'pending';
           
           $user->save();            
           // upon successful registration create profile for user so user can edit their profile later
           if($user){
 
             //implemented sms
             $country_code = $country->get_country_code($request['country']);
 
             $json_url = "http://api.ebulksms.com:8080/sendsms.json";
             $username = 'admin@livestock247.com';
             $apikey = '9f55c26a56608eaf6f3587b630513695921fa4ba';
 
             $sendername = 'FME';
             $messagetext = 'Kindly use this '.$reg_code.' code to verify your account on FME App';
 
                             $gsm = array();
             $country_code = $country_code;
             $arr_recipient = explode(',', $request['phone']);
 
             $generated_id = uniqid('int_', false);
             $generated_id = substr($generated_id, 0, 30);
             $gsm['gsm'][] = array('msidn' => $arr_recipient, 'msgid' => $generated_id);
 
             $mss = array(
             'sender' => $sendername,
             'messagetext' => $messagetext,
         
             );
             $request = array('SMS' => array(
             'auth' => array(
             'username' => $username,
             'apikey' => $apikey
             ),
             'message' => $mss,
             'recipients' => $gsm
             ));
 
             $json_data = json_encode($request);
             if($json_data) {
 
               $curl = curl_init();
               curl_setopt_array($curl, array(
               CURLOPT_URL => $json_url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => '',
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 0,
               //CURLOPT_FOLLOWLOCATION => true,
               CURLOPT_SSL_VERIFYPEER => false,
               //CURLOPT_CAINFO, "C:/xampp/cacert.pem",
               //CURLOPT_CAPATH, "C:/xampp/cacert.pem",
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => 'POST',
               CURLOPT_POSTFIELDS =>$json_data,
                 CURLOPT_HTTPHEADER => array(
                   'Content-Type: application/json'
                 )
               ));
               $response = curl_exec($curl);
               $err = curl_error($curl);
               $res = json_decode($response, true);
             }
             if($err){
               $status = false;
               $message ="message is not sent";
               $error = $err;
               $data ="";
               $code = 400;
               return ResponseBuilder::result($status, $message, $error, $data, $code);
             }else if($response){
               $status = true;
               $message ="message sent successfully";
               $error = "";
               $data = "";
               $code = 200;                
               return ResponseBuilder::result($status, $message, $error, $data, $code);
             }
             else {
               $status = false;
               $message ="your phone number can not be determined";
               $error = "";
               $data = "";
               $code = 400;                
               return ResponseBuilder::result($status, $message, $error, $data, $code); 
             }              
         
           }else{
             $status = false;
             $message ="user not save";
             $error = "";
             $data = "";
             $code = 400;                
             return ResponseBuilder::result($status, $message, $error, $data, $code);          
           }
       }else{
         $status = false;
         $message ="otp not generated and saved";
         $error = "";
         $data = "";
         $code = 400;                
         return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }    

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
        $orderRequest = new OrderRequest();
        $orderRequest->user_id = $request->user_id;
        $orderRequest->name = $request->name;
        $orderRequest->phone = $request->phone;
        $orderRequest->amount = $request->amount;
        $orderRequest->location = $request->location;
        $orderRequest->agent_id = $request->agent_id;
        $orderRequest->sp_id =$request->service_provider;
        $orderRequest->service_type =$request->service_type;
        $orderRequest->status = "pending";
        $orderRequest->save();

        $status = true;
        $message ="Your ".$request->service_type." request is successfull";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);               
    }

}

}//class