<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Profile;
use App\Otp;
use Carbon\Carbon;
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

     protected function validator(array $request)
    {
          return Validator::make($request, [
            'ip'        => ['string', 'max:255'],
            'country'   => ['string', 'max:255'],
            'user_type' => ['string', 'max:255'],
            'country'   => ['string', 'max:255'],
            'name'      => ['required','string', 'max:255'],
            'farm_type' => ['string', 'max:255'],
            'service_type' => ['string', 'max:255'],
            'country_code' => ['string', 'max:255'],
            'phone'     => ['required', 'string', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    public function createUser(Request $request){
 
        //generate random code insert to otp table send otp to user phone
          $reg_code   = str_random(6);//generate unique 6 string
          $otp            = new Otp();
          $otp->code      = $reg_code;

          $otp->save();

          //send otp as sms to user phone here 

        $user = new User();
        $user->ip          = $request['ip']; //hidden input field. auto get the user ip
        $user->country     = $request['country'];  // hidden field. auto get the user country from his ip
        $user->user_type   = $request['user_type']; // can select from role table
        $user->name        = $request['name']; // required 
        $user->farm_type   = $request['farm_type']; //select fron db 'farmer'
        $user->service_type = $request['service_type']; //select fron db 'service'
        $user->country_code = $request['country_code']; // select from db
        $user->phone       = $request['phone']; 
        $user->reg_code    = $reg_code;
        $user->password    = Hash::make($request['password']);
        $user->status      = 'pending';
        
        $user->save();

        // upon successful registration create profile for user so user can edit their profile later
        if($user){
        // users profile page
          $profile = new Profile();
          $profile->user_id         = $user->id; //get inserted user id
          $profile->email           = $request->input('email'); //optional 
          $profile->business_name   = $request->input('business_name'); // optional
          $profile->address         = $request->input('address'); // required 
          $profile->location        = $request->input('location'); // required. fetch from lacation table
          $profile->bank_name       = $request->input('bank_name'); // optional
          $profile->account_name    = $request->input('account_name'); // optional
          $profile->account_number  = $request->input('account_number'); // optional 
          
          $profile->save(); 

        }

      return response()->json($user);
 
  }

  //update user with  otp
  public function verifyUser(Request $request){
      
      //Input::get('code')
      $getCode = $request->input('code');

      //check if exist
      $otp =  Otp::where('code', $getCode)->exists();

      $user  = User::where('reg_code', $otp)
              ->update([
                'status' =>'verify'
              ]);
     
      return response()->json($user);
  } 


  public function deleteUser($id){
      $user  = User::find($id);
      $user->delete();
 
      return response()->json('Removed successfully.');
  }


  public function index(){
 
      $users  = User::all();
 
      return response()->json($users);
 
  }
}//class
