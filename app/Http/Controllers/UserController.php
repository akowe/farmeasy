<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\User;
use App\UserProfile;
use App\Otp;
use App\Role;
use App\Country;
use App\FarmType;
use App\Rating;
use App\FeedBack;
use App\ServiceType;
use App\OrderRequest;
use App\BecomeAgent;
use Carbon\Carbon;
use Carbon\Profile;
use App\Payment;
use App\Price;


use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class UserController extends Controller
{
  
  public function __construct()
  {
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
     
       //$user_type = 'admin';
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
       $user->password    = Hash::make($request->password);
       $user->status      = 'pending';
       
       $user->save();            
       
       // upon successful registration create profile for user so user can edit their profile later
         $profile            = new UserProfile();
         $profile->user_id   = $user->id;
  
         $profile->save();
        
        
       if($user){

             //implemented sms
                  $country_code = $country->get_country_code($request['country']);
                 // https://api.ebulksms.com:4433/sendsms.json
                  //http://api.ebulksms.com:8080/sendsms.json
                  $json_url = "https://api.ebulksms.com:4433/sendsms.json";
                  $username = 'admin@riceafrika.com';
                  $apikey = 'eda594a3b4f30a20857dd9a80fcde0ff69840cb7';
      
                  $sendername = 'FarmEASY';
                  $messagetext = 'Kindly use '.$reg_code.' to verify your account on FarmEASY App';
      
                  
                  $gsm = array();

                  // remove the + sign from countrycode. ebulksms requiment for sending
                  $country_code = trim($country_code->country_code, "+");  

                  //remove first "0" from phone number             
                  $arr_recipient = explode(',', trim($request['phone'], "0"));
                  $phone =implode(',',$arr_recipient);

                  $generated_id = uniqid('int_', false);
                  $generated_id = substr($generated_id, 0, 30);
                  $gsm['gsm'][] = array('msidn' => $country_code.$phone, 'msgid' => $generated_id);
      
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
                    $message ="sms is not sent";
                    $error = '';
                    $data ="";
                    $code = 400;
                    return ResponseBuilder::result($status, $message, $error, $data, $code);
                  }else if($response){
                    $status = true;
                    $message ="sms sent successfully";
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
  

  public function verifyAgent(Request $request){

    // validation
    $validator =Validator::make($request->all(), [
      'code' => 'required',
      'password' => 'required|confirmed'

    ]);      

      if($validator->fails()){
      $status = false;
      $message ="";
      $error = $validator->errors()->first();
      $data = "";
      $code = 400;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
      } 
       $getCode = $request->input('code');

       //check if exist
         $otp =  Otp::where('code', $getCode)->exists();
         if($otp){
           $user  = User::where('reg_code', $getCode)
           ->update([
             'status' =>'verified',
             'password' => Hash::make($request->password)
           ]);
 
           $user  = User::where('reg_code', $getCode)->first();
             
         // users profile page
           $profile = UserProfile::firstOrNew(['user_id' => $user->id]);
           $profile->user_id  = $user->id; //get inserted user id
           $profile->save(); 
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
      

     // validation
     $validator =Validator::make($request->all(), [
      'code' => 'required'

    ]);      

      if($validator->fails()){
      $status = false;
      $message ="";
      $error = $validator->errors()->first();
      $data = "";
      $code = 400;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }    
      //Input::get('code')
      $getCode = $request->input('code');

      //check if exist
       $user  = User::where('reg_code', $getCode)->first();

        $otp =  Otp::where('code', $getCode)->exists();
        if($otp && $user->status =="pending"){

          $user  = User::where('reg_code', $getCode)
          ->update([
            'status' =>'verified'
          ]);

          $user  = User::where('reg_code', $getCode)->first();
            
        // users profile page
          $profile = UserProfile::firstOrNew(['user_id' => $user->id]);
          $profile->user_id  = $user->id; //get inserted user id
          $profile->save(); 
          $status = true;
          $message ="verified";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }elseif($otp && $user->status =="verified"){
        
        $status = false;
        $message ="Your account is verified, kindly proceed to login.";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }
       elseif($otp && $user->status =="remove"){
        
        $status = false;
        $message ="Your account was deleted. Kindly contact admin for re-activation.";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }

       else{
        $status = false;
        $message ="Kindly put your right verification code";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
       }
 
  } 


  public function deleteUser(Request $request){

    //  // validation
     $validator =Validator::make($request->all(), [
      "password" =>'required'
    ]);      

    //   if($validator->fails()){
    //   $status = false;
    //   $message ="";
    //   $error = $validator->errors()->first();
    //   $data = "";
    //   $code = 400;                
    //   return ResponseBuilder::result($status, $message, $error, $data, $code);   
    //   } 

      //$id = $request->id;
      $id = Auth::user()->id;
      $user  = User::where('id', $id)->first();
     // if(Gate::allows('destroy', $user)){
     //    // validation
     //    $validator =Validator ::make($request->all(), [
     //      'id' => 'required'
     //    ]);  

        if($validator->fails()){
          $status = false;
          $message ="";
          $error = $validator->errors()->first();
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
        }

        //user enter password for double confirmation before deleting user
        elseif (Hash::check($request->input('password'),$user->password)){ 
         
          if($user->user_type =="1"){
            $status = true;
            $message ="Oops Can't delete the admin";
            $error = "";
            $data = "";
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);         
           }elseif($user->password && $user !="1"){
            if($user->status =="remove"){
              $status = false;
              $message ="This user has already been deleted";
              $error = "";
              $data = "";
              $code = 401;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
             }else{
              $user  = User::where('id', $id)
              ->update([
                'status' =>'remove'
              ]);
              $status = true;
              $message ="Your account was successfully deleted";
              $error = "";
              $data = "";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
            } 
           }
        }else{
             $status = false;
             $message ="Invalid password";
             $error = "";
             $data = "";
             $code = 401;                
             return ResponseBuilder::result($status, $message, $error, $data, $code);  
           }

     // }else{
     //  $status = false;
     //  $message ="Not Authorized to delete a user";
     //  $error = "";
     //  $data = "";
     //  $code = 401;                
     //  return ResponseBuilder::result($status, $message, $error, $data, $code);
     // }
     
  }
  
  // update profile details
  public function updateProfile(Request $request){
 
    // validation
    $validator =Validator ::make($request->all(), [
      
      'address'     => 'required',
      'location'    => 'required',
      'bank_name'   => 'string',
      'account_name' => 'string',
      'account_number' => 'string',
      'email'         => 'string',
      'business_name' =>'string',
      ]);  

      if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }else{ 

      // get array farm and service type
        
        // $farm_type = array(
        // 'farm_type' => $request->farm_type,
      
        // );

        //  $service_type = array(
        //  'service_type' => $request->service_type,
        // );
          $user_id = Auth::user()->id;

          $profile  = UserProfile::where('user_id', $user_id)->update([
          'user_id' =>  $user_id,
          'email' => $request->email,
          'business_name'   => $request->business_name,
          'address' => $request->address,
          'location' => $request->location,
          'bank_name' => $request->bank_name,
          'account_name' => $request->account_name, 
          'account_number'  => $request->account_number,
          'farm_type'     => json_encode($request['farm_type']),
          'service_type' => json_encode($request['service_type']),
          'profile_update_at' => date('Y-m-d h:i:s')
      ]);

      if($profile)

      {
        // also udpate the user farm type or service ttype in user table
        User::where('id', Auth::user()->id)->update([
        'farm_type'     => json_encode($request['farm_type']),
        'service_type' => json_encode($request['service_type']),
      ]);

      $status = true;
      $message ="Profile successfully updated";
      $error = "";
      $data = $profile;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
      }
      else{
        $status = false;
        $message ="No user with this profile";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }
  
    }
  }

    // get profile details
    public function getProfile(Request $request){
      //$id =  $request->id;
      $id = Auth::user()->id;
      $profile = UserProfile::where('user_id', $id)->first();
      if($profile){
        $status = true;
        $message ="";
        $error = "";
        $data = $profile;
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }else{
        $status = false;
        $message ="No user with this profile";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);        
      }

    } 

// this should be for admin only
  public function index(){
    $user_type = '1';
      $users  = User::where('user_type', '!=', $user_type)->orderByDesc('created_at')->get();
      $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    }
    
 
  

  public function user(Request $request){
  $id = Auth::user()->id;
    //$id =  $request->id;
    $user = User::where('id', $id)->first();
    if($user){
      if($user->status =="remove"){
        $status = false;
        $message ="User with id ".$request->id." is not found";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }else{
        $status = true;
        $message ="";
        $error = "";
        $data = $user;
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }

    }else{
      $status = false;
      $message ="User with id ".$request->id." is not found";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);      
    }
   
   

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
     $country = new Country();
        //check if exist
    $user =  User::where('phone', $request->phone)->exists();

      if($user){

        // generate new otp
        $password_reset_code  =random_int(100000, 999999); //random_code(6);
        $otp            = new Otp();
        $otp->code      = $password_reset_code;
        $otp->save();

        //update user with otp
        User::where('phone', $request['phone'])
              ->update([
                'reg_code'=> $password_reset_code 
              ]);

      
         //implemented sms

                  $json_url = "https://api.ebulksms.com:4433/sendsms.json";
                  $username = 'admin@riceafrika.com';
                  $apikey = 'eda594a3b4f30a20857dd9a80fcde0ff69840cb7';
                  // $username = 'admin@livestock247.com';
                  // $apikey = '9f55c26a56608eaf6f3587b630513695921fa4ba';
      
                  $sendername = 'FarmEASY';
                  $messagetext = 'Kindly use  '.$password_reset_code.'  to reset your password on FarmEASY App';
      
                  
                  $gsm = array();

                  //remove first "0" from phone number             
                  $arr_recipient = explode(',', trim($request['phone'], "0"));
                  $phone =implode(',',$arr_recipient);

                  $generated_id = uniqid('int_', false);
                  $generated_id = substr($generated_id, 0, 30);
                  $gsm['gsm'][] = array('msidn' => $phone, 'msgid' => $generated_id);
      
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
                    $message ="sms is not sent";
                    $error = '';
                    $data ="";
                    $code = 400;
                    return ResponseBuilder::result($status, $message, $error, $data, $code);
                  }elseif($response){
                    $status = true;
                    $message ="sms sent successfully";
                    $error = "";
                    $data = "";
                    $code = 200;                
                    return ResponseBuilder::result($status, $message, $error, $data, $code);
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
      'phone' => 'required|min:11|numeric',
      'new_password' => 'required',
      'reset_code' => 'required'
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
        $message ="Password successfully reset";
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

     //change to new passowrd when logged in
     public  function userChangePassword(Request $request){

      //validattion
      $validator =Validator ::make($request->all(), [
        'old_password' => 'required|min:11|numeric',
        'new_password' => 'required'
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
        $user =  User::where('password',  Hash::make($request->old_password))->exists();
        
        if($user){
  
          $user  = User::where('password',  Hash::make($request->old_password))
          ->update([
            'password' => Hash::make($request['new_password'])
          ]);
          $status = true;
          $message ="Password successfully changed";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
         
        }else{
          $status = false;
          $message ="Old password is wrong";
          $error = "";
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
         
        }
  
  
     } 

  // authenticate user for login
  public function authenticateUser(Request $request){

    // validation
    $validator =Validator ::make($request->all(), [
    'phone' => 'required|min:11|numeric',
    'password' => 'required'

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
      if($user->status =="remove"){
         $status = false;
          $message ="This account was deleted. Contact admin";
          $error = "";
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }
      elseif($user->status =="verified"){
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

    $status = true;
    $message ="";
    $error = "";
    $data =$country_code;
    $code = 200; 
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


    // feedback
    public function feedBack(Request $request){
      // validation
          $validator =Validator ::make($request->all(), [
  
              // 'subject' => 'required',
              // 'service_type' => 'required',
              'message' => 'required'
             
          ]);   
             
          if($validator->fails()){
          $status = false;
          $message ="";
          $error = $validator->errors()->first();
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
          }else{
              $feedback = new FeedBack();
              // $feedback->subject = $request->subject;
              // $feedback->service_type = $request->service_type;
              $feedback->message = $request->message;
              $feedback->user_id = Auth::user()->id;
              $feedback->save();
              $status = true;
              $message ="Feedback successfully submitted";
              $error = "";
              $data = "";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
          
          }            
}
 
  // fetch all feedbacks
  public function getFeedBack(){
 
    $feedbacks  = FeedBack::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $feedbacks;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



 public function BecomeAnAgent(Request $request){
      // validation
          $validator =Validator ::make($request->all(), [
  
            'name'        => 'required',
            'location'    => 'required',
            'email'       => 'string',
            'phone'       => 'required'
             
          ]);   
             
          if($validator->fails()){
          $status = false;
          $message ="";
          $error = $validator->errors()->first();
          $data = "";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);   
          }else{
              $agent = new BecomeAgent();
              $agent->name    = $request->name;
              $agent->phone   = $request->phone;
              $agent->location = $request->location;
              $agent->email   =  $request->email;
              $agent->save();
              
              $status = true;
              $message ="Request successfull, You will be contacted shortly.";
              $error = "";
              $data = "";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
          
          }            
}
 
public function rating(Request $request){
  // validation
      $validator =Validator ::make($request->all(), [

        'rating'        => 'required',
         
      ]);   
         
      if($validator->fails()){
      $status = false;
      $message ="";
      $error = $validator->errors()->first();
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);   
      }else{
          $agent = new Rating();
          $agent->rating    = $request->rating;
          $agent->user_id   = Auth::user()->id;
          $agent->save();
          
          $status = true;
          $message ="Thank you for rating us";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
    
      }            
}

       // get rating
       public function getRating(){
        $user_id =  Auth::user()->id;
        $rating = Rating::where("user_id", $user_id)->get();
      
        if($rating){
       
          $status = true;
          $message ="";
          $error = "";
          $data = $rating;
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
      
        }else{
          $status = false;
          $message ="";
          $error = "";
          $data = "No rating found";
          $code = 401;                
          return ResponseBuilder::result($status, $message, $error, $data, $code); 
      
        }

      }

}//class      