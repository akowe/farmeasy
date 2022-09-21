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
use App\ServiceProduct;
use App\OrderRequest;
use App\Country;
use App\ServiceType;
use Carbon\Carbon;
use Carbon\Profile;
class ServiceController extends Controller
{

  public function __construct()
  {
      //create superadmin  

      // $user = User::where("phone","08188373898")->first();
      // if($user){
      //   $status = false;
      //   $message ="Phone number already taken";
      //   $error = "";
      //   $data = "";
      //   $code = 401;                
      //   return ResponseBuilder::result($status, $message, $error, $data, $code); 
      // }else{
      //   $user = User::firstOrNew(['name' => 'superadmin', 'phone' => '08188373898']);
      //   $user->name ="superadmin";
      //   $user->phone ="08188373898";
      //   $user->country      = 'Nigeria';
      //   $user->country_code ='+234';
      //   $user->user_type   =  '1'; // can select from role table
      //   $user->password    = Hash::make('password');
      //   $user->status      = 'verified';
      //   $user->save();
      //}      

  
  }
    public function createService(Request $request){


      // validation
      $validator =Validator::make($request->all(), [
        'name' => 'required',
        'phone' => 'required|numeric|unique:users,phone',
        'service_type' => 'required',
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
      
            if($otp->save()){
                //send otp as sms to user phone here 
              
                $user_type = 'service';
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
                $user->user_type   =  '5'; // can select from role table
                $user->service_type   = $request['service_type']; //select fron db 'service' 
                $user->password    = Hash::make($request['password']);
                $user->status      = 'pending';
                
                $user->save();            
                // upon successful registration create profile for user so user can edit their profile later
                if($user){
                  //implemented sms
                  $country_code = $country->get_country_code($request['country']);
      
                  $json_url = "http://api.ebulksms.com:8080/sendsms.json";
                  $username = 'admin@livestock247.com';
                  $apikey = '9f55c26a56608eaf6f3587b630513695921fa4ba';
      
                  $sendername = 'FME';
                  $messagetext = 'Kindly use this '.$reg_code.' code to verify your account on FME App';
      
                                  $gsm = array();
                  $country_code = $country_code;
                  $arr_recipient = explode(',', ltrim($request['phone'], "0"));
      
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
                    $message ="message is not sent";
                    $error = $err;
                    $data ="";
                    $code = 400;
                    return ResponseBuilder::result($status, $message, $error, $data, $code);
                  }else if($response){
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



   // fetch service provider location
   public function getServiceProviderByLocation(Request $request){
     $location = $request->location;
     $service_type =$request->service_type;
     $service_providers = array();

    $profiles = UserProfile::where("location", $location)->get();

     if($profiles){
     
      foreach($profiles as $profile){
       
        $user = User::where(array("id" => $profile->user_id, "service_type"=>$service_type))->exists();
        if($user){
          $user = User::where(array("id" => $profile->user_id, "service_type"=>$service_type))->first();
          $service_providers[] = array("id"=>$user->id, "name"=>$user->name);
        }     
      }

      $status = true;
      $message ="";
      $error = "";
      $data = $service_providers;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
     }else{
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);       
     }

  } 

 // all farmer and agent request by location
 public function allFarmerAgentRequestByLocation(Request $request){
  $location = $request->location;
  $all_request = OrderRequest::where("location", $location)->get();
  if($all_request){
    $status = true;
    $message ="";
    $error = "";
    $data = $all_request;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }else{
    $status = false;
    $message ="";
    $error = "";
    $data = "No request currently available";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }

}  


  //fetch all service types
  public function allServiceTypes(){
 
    $all_service_types  = ServiceType::all();

    $status = true;
    $message ="";
    $error = "";
    $data = $all_service_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 


  } 


  //add new product
  public function addProduct(Request $request){
    // validation
    $validator =Validator ::make($request->all(), [
      'name' => 'required',
      'product_type' => 'required',
      'quantity' => 'required',
      'price' => 'required',
      'rent_sell' => 'required',
      'description' => 'required',
      'user_id' => 'required'
  

  ]);      
  if($validator->fails()){
  $status = false;
  $message ="";
  $error = $validator->errors()->first();
  $data = "";
  $code = 401;                
  return ResponseBuilder::result($status, $message, $error, $data, $code);   
  }else{
      $product = new ServiceProduct();
      $product->product_name = $request->name;
      $product->product_type = $request->product_type;
      $product->qty = $request->quantity;
      $product->price = $request->price;
      $product->rent_sell = $request->rent_sell;
      $product->description = $request->description;
      $product->user_id = $request->user_id;
      $product->save();

      $status = true;
      $message ="You have successfully created ".$request->name." as a product";
      $error = "";
      $data = "";
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
  } 
}


  //fetch all product
  public function allProducts(){
    $all_products  = ServiceProduct::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_products;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 


  }   


}