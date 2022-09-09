<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Auth;
use App\User;
use App\UserProfile;
use App\Otp;
use App\Role;
use App\Country;
use App\FarmType;
use App\ServiceType;
use Carbon\Carbon;
use Carbon\Profile;
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

      $user  = User::where('reg_code', $getCode)
              ->update([
                'status' =>'verified'
              ]);
     
      return response()->json(["user"=>$user, "message"=>"Account successfully verified"]);
  } 


  public function deleteUser(Request $request){
    $id = $request->id;
      $user  = User::find($id);
      $user->delete();
 
      return response()->json('Removed successfully.');
  }
  
  // update profile details
  public function updateProfile(Request $request){
    $user_id = $request->user_id;
    $profile = array(
      'email' => $request->input('email'), 
      'business_name'   => $request->input('business_name'),
      'address' => $request->input('address'),
      'location' => $request->input('location'),
      'bank_name' => $request->input('bank_name'),
      'account_name' => $request->input('account_name'), 
      'account_number'  => $request->input('account_number')
    );

    $profile  = UserProfile::where('user_id', $user_id)
    ->update($profile);

      return response()->json($profile);  
  }

    // get profile details
    public function getProfile(Request $request){
      $id =  $request->id;
      $profile = UserProfile::where('user_id', $id)->first();
        return response()->json($profile);  
    } 

  public function index(){
 
      $users  = User::all();
 
      return response()->json($users);
 
  }

  public function user(Request $request){
 
    $id =  $request->id;
    $user = User::where('id', $id)->first();
      return response()->json($user);

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
        $password_reset_code  = $this->random_code(6);
        $otp            = new Otp();
        $otp->code      = $password_reset_code;
        $otp->save();

        $user  = User::where('phone', $request->phone)
        ->update([
          'reg_code' =>$password_reset_code
        ]);
        $query = @unserialize (file_get_contents('http://ip-api.com/php/'));
        if ($query && $query['status'] == 'success') {
         $query_country =$query['country'];
        }else{
          return response()->json(["message"=>"we can't identify your location, kindly try later"]);
        }

        $sms_api_key = 'TLLXf8lLQZpsvuFouxWoN89YzoxL23RyXDUtDKAgNcniDpgGdpMUkgqxilO0tW';
        $sms_message = 'Kindly use this '.$password_reset_code.' code to reset your password.'. "\r\n";
        $country_code = $country->get_country_code($query_country);
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
              $data = array(
                  'message' => $sms_message,
                  'date' => date('Y-m-d H:i:sa'),
                  'recipient' => $recipient,
                  'user' => $user_id
              );
              return response()->json([ "message"=>"Message successfully sent"]);
            }else{
              return response()->json([ "message"=>"Message is not sent"]);
            }
          }
                        
        } else{
          return response()->json([ "message"=>"your phone number can not be determined"]);
        }
      }


   }

   //reset new passowrd
   public  function userResetPassword(Request $request){

    //validattion
    $this->validate($request, [
      'phone' => 'required|min:11|numeric',
      'new_password' => 'required',
      'reset_code' => 'required'
      ]);
        //check if exist
      $user =  User::where('reg_code', $request->reset_code)->exists();
      if($user){

        $user  = User::where('phone', $request->phone)
        ->update([
          'password' => Hash::make($request['new_password'])
        ]);

        return response()->json(['message'=>'Password successfully change'], 200);
      }else{
        return response()->json(['message'=>'Reset code is wrong'],401);
      }


   }

  // authenticate user for login
  public function authenticateUser(Request $request){
      // validation
      $this->validate($request, [
        'phone' => 'required|min:11|numeric',
        'password' => 'required'

    ]);
    $condition= array('phone'=>$request->phone);
    $user = User::where($condition)->first();

      if ( Hash::check($request->input('password'), $user->password) && $user->status =='verified') {
         return response()->json(['status' => 'verified', 'user'=>$user],200);
      }else{
        return response()->json(['status' => 'fail', 'message'=>'Phone number or password is wrong'],401);
      }
   }

}