<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Profile;
use App\Otp;
use App\Country;
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
         
          //send otp as sms to user phone here 

          //get user ip
          $ipaddress = '';
          if (isset($_SERVER['HTTP_CLIENT_IP'])) {
              $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
          } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
              $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
          } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
              $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
          } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
              $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
          } else if (isset($_SERVER['HTTP_FORWARDED'])) {
              $ipaddress = $_SERVER['HTTP_FORWARDED'];
          } else if (isset($_SERVER['REMOTE_ADDR'])) {
              $ipaddress = $_SERVER['REMOTE_ADDR'];
          } else {
              $ipaddress = 'UNKNOWN';
          }
          
          //get lcountry of any network
          $getloc = json_decode(file_get_contents("http://ipinfo.io/"));
          $country= $getloc->country;
          $city = explode(",", $getloc->region); // -> '32,-72' becomes'32','-72'
        
        if (Country::where('country', $country)->exists()) {
   
        $user = new User();
        $user->ip          = $ipaddress; //hidden input field. auto get the user ip
        $user->country     = $country;  // hidden field. auto get the user country from his ip
        $user->user_type   = $request['user_type']; // can select from role table
        $user->name        = $request['name']; // required 
        $user->farm_type   = $request['farm_type']; //select fron db 'farmer'
        $user->service_type = $request['service_type']; //select fron db 'service'
        $user->country_code = $request['country_code']; // select from country table
        $user->phone       = $request['phone']; 
        $user->reg_code    = $reg_code;
        $user->password    = Hash::make($request['password']);
        $user->status      = 'pending';
        
        $user->save();

          }

          else{
            return response('FME app not available in your country');
          }

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


public function updateUser(Request $request, $id){

    $getCode = $request->input('reg_code');

    //check if exist
    //   $otp =  User::where('reg_code', $getCode)->exists();

    // if($otp)
      //{
      $user  = User::find($id);
      $user->status = 'verify';
     
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


  public function index(){
 
      $users  = User::all();
 
      return response()->json($users);
 
  }
}//class
