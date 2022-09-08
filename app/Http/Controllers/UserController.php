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

    public function createUser(Request $request){
  

        if($request->account_type =="farmer"){
            // validation
            $this->validate($request, [
              'name' => 'required',
              'phone' => 'required|min:11|numeric|unique:users,phone',
              'farm_type' => 'required',
              'password' => 'required|confirmed'

          ]);
          //generate random code insert to otp table send otp to user phone
          $reg_code   = str_random(6);//generate unique 6 string
         
          //send otp as sms to user phone here 

        
          $user = new User();
          $role = new Role();
          $country = new Country();
          $user->ip          = $request['ip']; //hidden input field. auto get the user ip
          $user->country     = $request['country'];  // hidden field. auto get the user country from his ip
          $user->name        = $request['name']; // required 
          $user->country_code = $country->get_country_code($request['country']); // select from db
          $user->phone       = $request['phone']; 
          $user->reg_code    = $reg_code;

          $user_type = 'farmer';
          $user->user_type   =  $role->get_role($user_type); // can select from role table
          $user->farm_type   = $request['farm_type']; //select fron db 'farmer'
          $user->password    = Hash::make($request['password']);
          $user->status      = 'pending';
          $user->save();         

        }else if($request->account_type =="service provider"){

            // validation
            $this->validate($request, [
              'name' => 'required',
              'phone' => 'required|min:11|numeric|unique:users,phone',
              'service_type' => 'required',
              'password' => 'required|confirmed'

          ]);      

          //generate random code insert to otp table send otp to user phone
          $reg_code   = str_random(6);//generate unique 6 string
          $otp            = new Otp();
          $otp->code      = $reg_code;

          $otp->save();

          //send otp as sms to user phone here 
        
          $user = new User();
          $role = new Role();
          $country = new Country();
          $user->ip          = $request['ip']; //hidden input field. auto get the user ip
          $user->country     = $request['country'];  // hidden field. auto get the user country from his ip
          $user->name        = $request['name']; // required 
          $user->country_code = $country->get_country_code($request['country']); // select from db
          $user->phone       = $request['phone']; 
          $user->reg_code    = $reg_code;

          $user_type = 'service';
          $user->user_type   =  $role->get_role($user_type); // can select from role table
          $user->service_type = $request['service_type']; //select fron db 'service' 
          $user->password    = Hash::make($request['password']);
          $user->status      = 'pending';
          $user->save();            
        }


          }

          else{
            return response('FME app not available in your country');
          }

        // upon successful registration create profile for user so user can edit their profile later
        if($user){
        // users profile page
          $profile = new UserProfile();
          $profile->user_id  = $user->id; //get inserted user id
          $profile->save(); 

        }

      return response()->json($user);
 
  }


public function updateUser(Request $request, $id){


      $user  = User::where('reg_code', $otp)
              ->update([
                'status' =>'verified'

     
      $user->save();
    // }
    // else{
    //         return response('Invalid code');
    //       }
 
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
  
  // fetch all farm types
  public function allFarmTypes(){
 
    $all_farm_types  = FarmType::all();

    return response()->json($all_farm_types);

  } 
  
  //fetch all service types
  public function allServiceTypes(){
 
    $all_service_types  = ServiceType::all();

    return response()->json($all_service_types);

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
          'reset_code' =>$password_reset_code
        ]);
        // just for testing, will remove it when bulk sms is implemented
        return response()->json(['reset_code'=>$password_reset_code]);
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
      $user =  User::where('reset_code', $request->reset_code)->exists();
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