<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserProfile;
use App\Otp;
use App\Role;
use App\Country;
use App\FarmType;
use App\ServiceType;
use Carbon\Carbon;
use Carbon\Profile;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class UserController extends Controller
{
    //
   public function getOtp(Request $request){
        //generate new otp
          $code   = str_random(6);
          $new_otp        = new Otp();
          $new_otp->code  = $code;
          $new_otp->save();

        //send otp to user phone

    }


  //update user with  otp
  public function verifyUser(Request $request){
      
      //Input::get('code')
      $getCode = $request->input('code');

      //check if exist
        $otp =  Otp::where('code', $getCode)->exists();
        if($otp){
          $user  = User::where('reg_code', $getCode)
          ->update([
            'status' =>'verified'
          ]);

        $status = true;
        $message ="verified";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }else{
        
        $status = false;
        $message ="kindly put your right verification code";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }
 
  } 


  public function deleteUser(Request $request){
    $id = $request->id;
      $user  = User::find($id);
      $user->delete();
      $status = true;
      $message ="Removed successfully";
      $error = "";
      $data = "";
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
     
  }
  
  // update profile details
  public function updateProfile(Request $request){
 // validation
            $this->validate($request, [
              'address' => 'required',
              'location' => 'required',

          ]);   

    $user_id = $request->user_id;
    $profile = array(
      'email' => $request->input('email'), 
      'business_name'   => $request->input('business_name'),
      'address' => $request['address'],
      'location' => $request['location'],
      'bank_name' => $request->input('bank_name'),
      'account_name' => $request->input('account_name'), 
      'account_number'  => $request->input('account_number')
    );

    $profile  = UserProfile::where('user_id', $user_id)
    ->update($profile);
    $status = true;
    $message ="";
    $error = "";
    $data = $profile;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);    
  }

    // get profile details
    public function getProfile(Request $request){
      $id =  $request->id;
      $profile = UserProfile::where('user_id', $id)->first();
      $status = true;
      $message ="";
      $error = "";
      $data = $profile;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    } 

  public function index(){
 
      $users  = User::all();
 
      $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
 
  }

  public function user(Request $request){
 
    $id =  $request->id;
    $user = User::where('id', $id)->first();
    $status = true;
    $message ="";
    $error = "";
    $data = $user;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);   
   

 }

  function random_code($length)
  {
    return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
  }
  
  

   //forgot passowrd
  public  function userForgotPassword(Request $request){

    //validattion
    $this->validate($request, [
      'phone' => 'required|min:11|numeric',
      ]);
        //check if exist
      $user =  User::where('phone', $request->phone)->exists();
      if($user){

        // bulk sms will be replaced here
        $password_reset_code  =random_int(100000, 999999); //random_code(6);
        $otp            = new Otp();
        $otp->code      = $password_reset_code;
        $otp->save();

        /*$user  = User::where('phone', $request->phone)
        ->update([
          'reg_code' =>$password_reset_code
        ]);
          $country = new Country();
        $query = @unserialize (file_get_contents('http://ip-api.com/php/'));
        if ($query && $query['status'] == 'success') {
         $query_country =$query['country'];
        }else{
          return response()->json(["message"=>"we can't identify your location, kindly try later"]);
        }*/
        $country_code = $country->get_country_code($request->country);
        $sms_api_key = 'TLLXf8lLQZpsvuFouxWoN89YzoxL23RyXDUtDKAgNcniDpgGdpMUkgqxilO0tW';
        $sms_message = 'Kindly use this '.$password_reset_code.' code to reset your password.'. "\r\n";
        //$country_code = $country->get_country_code($query_country);
        $payload = array(   
          'to'=>$country_code.ltrim($request['phone'], '0'),
          'from'=>'fastbeep',
          'sms'=>$sms_message,
          'channel'=> 'generic',
          'type'=>'plain',
          'api_key'=>$sms_api_key, 
        );
        $post_data = json_encode($payload);   
            
        if (isset($request['phone']) && !empty($request['phone'])) {
          $curl = curl_init();
          curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.ng.termii.com/api/sms/send',
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
          CURLOPT_POSTFIELDS =>$post_data,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
          ));
          $response = curl_exec($curl);
          $err = curl_error($curl);
          $res = json_decode($response, true);
          
          if($err){
            return response()->json(["error"=>$err, "message"=>"Message is not sent"]);
          }else{
            if($response){
              $status = true;
              $message ="Message successfully sent";
              $error = "";
              $data = "";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);   
             
            }else{
              $status = true;
              $message ="Message is not sent";
              $error = "";
              $data = "";
              $code = 400;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);   
                    
            }
          }
                        
        } else{
          $status = false;
          $message ="Phone number can not be determined";
          $error = "";
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
         
        }
      }


   }

   //reset new passowrd
   public  function userResetPassword(Request $request){

    //validattion
    $validator =Validator ::make($request->all(), [
      'phone' => 'required|numeric',
      'new_password' => 'required',
      'reset' => 'reuired'
    ]);      
   if($validator->fails()){
    $status = false;
    $message ="";
    $error = $validator->errors()->first();
    $data = "";
    $code = 401;                
    ResponseBuilder::result($status, $message, $error, $data, $code);   
   } 
        //check if exist
      $user =  User::where('reg_code', $request->reset_code)->exists();
      if($user){

        $user  = User::where('phone', $request->phone)
        ->update([
          'password' => Hash::make($request['new_password'])
        ]);
        $status = true;
        $message ="Message successfully sent";
        $error = "";
        $data = "";
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
       
      }else{
        $status = false;
        $message ="Reset Code is wrong";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
       
      }


   }

  // authenticate user for login
  public function authenticateUser(Request $request){
      // validation
             // validation
             $validator =Validator ::make($request->all(), [
              'phone' => 'required|numeric',
              'password' => 'required|confirmed'

          ]);      
           if($validator->fails()){
            $status = false;
            $message ="";
            $error = $validator->errors()->first();
            $data = "";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);   
           } 
      $condition= array('phone'=>$request->phone);
      $user = User::where($condition)->first();
      if($user){
         if($user->status =="verified"){
          if (Hash::check($request->input('password'),$user->password)) {
            $apikey = base64_encode(str_random(40));
            User::where('phone', $request->input('phone'))->update(['api_key' => $apikey]);
            $status = true;
            $message ="";
            $error = "";
            $data = $apikey;
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code); 
          }else{
            $status = false;
            $message ="Kindly provide the right password";
            $error = "";
            $data = "";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);             
          }
         }else{
          $status = false;
          $message ="Kindly verify your account";
          $error = "";
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);         
         }
      }else{
        $status = true;
        $message ="Kindly put the right phone number";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);        
      }
      
   }

   // fetch all countries
   public function allCountries(){
 
    $countries = Country::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $countries;
    $code = 200; 
    return ResponseBuilder::result($status, $message, $error, $data, $code);  

  }  
   public function logout(){

    if(Auth::user()){
 
      $status = true;
      $message ="Successfully logout";
      $error = "";
      $data ="";
      $code = 200; 
      $user = Auth::user();
      $user->api_key = null;
      $user->save();
      return ResponseBuilder::result($status, $message, $error, $data, $code);  

    }else{
      $status = true;
      $message ="Already logout";
      $error = "";
      $data ="";
      $code = 200; 
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
     
    }

   }


   //fetch country code from databade. country table
    public function CountryCode(){
 
    $country_code  = Country::all();

    return response()->json($country_code);

  } 

}