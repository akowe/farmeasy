<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use App\User;
use App\UserProfile;
use App\Otp;
use App\Role;
use App\Country;
use App\FarmType;
use Carbon\Carbon;
use Carbon\Profile;
class FarmerController extends Controller
{

    public function createFarmer(Request $request){

            // validation
            $validator =Validator ::make($request->all(), [
              'name' => 'required',
              'phone' => 'required|numeric|unique:users,phone',
              'farm_type' => 'required',
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

          //generate random code insert to otp table send otp to user phone
          $reg_code   = random_int(100000, 999999); //random unique 6 figure str_random(6)
          $otp            = new Otp();
          $otp->code      = $reg_code;

          $otp->save();

          //send otp as sms to user phone here 
        
         $user_type = 'farmer';
          // $role = new Role();
          $country = new Country();
         
          $user = new User();
          $user->name         = $request['name']; // required 
          $user->country_code = $country->get_country_code($request->country); // select from db
          $user->country      = $request->country;
          $user->phone       = $request['phone']; 
          $user->reg_code    = $reg_code;
          $user->user_type   =  '4'; // can select from role table
          $user->farm_type   = $request['farm_type']; //select fron db 'service' 
          $user->password    = Hash::make($request['password']);
          $user->status      = 'pending';
          
          $user->save();            
          // upon successful registration create profile for user so user can edit their profile later
          if($user){
            // users profile page
              $profile = new UserProfile();
              $profile->user_id  = $user->id; //get inserted user id
              $profile->save(); 
    
            }// if $user
            
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
             if ($json_data) {

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
             } 

elseif($response){
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
        
 
  }//function

  
  // fetch all farm types
  public function allFarmTypes(){
 
    $all_farm_types  = FarmType::all();

    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);  
    

  } 

}