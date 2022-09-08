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
class FarmerController extends Controller
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

      $user  = User::where('reg_code', $otp)
              ->update([
                'status' =>'verified'
              ]);
     
      return response()->json($user);
  } 


  public function deleteUser($id){
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
    public function getProfile($id){
      $profile = UserProfile::where('user_id', $id)->first();
        return response()->json($profile);  
    } 

  public function index(){
 
      $users  = User::all();
 
      return response()->json($users);
 
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
        $user  = User::where('phone', $request->phone)
        ->update([
          'reg_code' =>$password_reset_code
        ]);
        // just for testing, will remove it when bulk sms is implemented
        return response()->json(['reg_code'=>$password_reset_code]);
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