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
              'phone' => 'required|min:11|numeric|unique:users,phone',
              'farm_type' => 'required',
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

          $user_type = 'farmer';
          $user->user_type   =  $role->get_role($user_type); // can select from role table
          $user->farm_type = $request['farm_type']; //select fron db 'service' 
          $user->password    = Hash::make($request['password']);
          $user->status      = 'pending';
          $user->save();            



        // upon successful registration create profile for user so user can edit their profile later
        if($user){
        // users profile page
          $profile = new UserProfile();
          $profile->user_id  = $user->id; //get inserted user id
          $profile->save(); 

        }

      return response()->json($user);
 
  }

  
  // fetch all farm types
  public function allFarmTypes(){
 
    $all_farm_types  = FarmType::all();

    return response()->json($all_farm_types);

  } 

}