<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
            $this->validate($request, [
              'name' => 'required',
              'phone' => 'required|numeric|unique:users,phone',
              'farm_type' => 'required',
              'password' => 'required|confirmed'

          ]);      

          //generate random code insert to otp table send otp to user phone
          $reg_code   = random_int(100000, 999999); //random unique 6 figure str_random(6)
          $otp            = new Otp();
          $otp->code      = $reg_code;

          $otp->save();

          //send otp as sms to user phone here 
        
          $user = new User();
          $role = new Role();
          $country = new Country();
          $user->ip = $request['ip']; //hidden input field. auto get the user ip
         
          $user->name        = $request['name']; // required 
          $query = @unserialize (file_get_contents('http://ip-api.com/php/'));
          if ($query && $query['status'] == 'success') {
           $query_country =$query['country'];
           $user->country     =$query_country;  // hidden field. auto get the user country from his ip
          }else{
            return response()->json(["message"=>"we can't identify your location, kindly try later"]);
          }

          $user->country_code = $country->get_country_code($request['country']); // select from db

          $user->phone       = $request['phone']; 
          $user->reg_code    = $reg_code;

          $user_type = 'farmer';
          $user->user_type   =  $role->get_role($user_type); // can select from role table
          $user->farm_type = $request['farm_type']; //select fron db 'service' 
          $user->password    = Hash::make($request['password']);
          $user->status      = 'pending';
          $sms_api_key = 'TLLXf8lLQZpsvuFouxWoN89YzoxL23RyXDUtDKAgNcniDpgGdpMUkgqxilO0tW';
          $sms_message = 'Kindly use this '.$reg_code.' code to verify your account on FME App';
          $country_code = $country->get_country_code($request['country']);
          $user->save();            
          // upon successful registration create profile for user so user can edit their profile later
          if($user){
            // users profile page
              $profile = new UserProfile();
              $profile->user_id  = $user->id; //get inserted user id
              $profile->save(); 
    
            }
            //implemented sms
          $payload = array(   
            'to'=>$country_code.''.ltrim($request['phone'], '0'),
            'from'=>'fastbeep',
            'sms'=>$sms_message,
            'channel'=> 'generic',
            'type'=>'plain',
            'api_key'=>$sms_api_key
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
            )
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $res = json_decode($response, true);
            
            if($err){
              return response()->json(["error"=>$err, "message"=>"Message is not sent"]);
            }else{
              if($response){
                return response()->json($user);
              }else{
                return response()->json([ "message"=>"Message is not sent"]);
              }
            }
                          
          } else{
            return response()->json([ "message"=>"your phone number can not be determined"]);
          }
        
 
  }

  
  // fetch all farm types
  public function allFarmTypes(){
 
    $all_farm_types  = FarmType::all();

    return response()->json($all_farm_types);

  } 

}