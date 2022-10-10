<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
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
use App\Payment;
use App\AgentNotification;
use App\FarmerNotification;
use App\ServiceNotification;
use App\Payment;
use App\Price;

class ServiceController extends Controller
{

  public function __construct()
  {
    
   
  }
    public function createService(Request $request){


      // validation
      $validator =Validator::make($request->all(), [
        'name' => 'required',
        'phone' => 'required|min:11|numeric|unique:users,phone',
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
                  $profile            = new UserProfile();
                  $profile->user_id   = $user->id;
                  $profile->save();

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

  // get service type by request id
  public function getServiceTypeByRequest(Request $request){
    $request_id = $request->id;
    $requestResult = OrderRequest::where("id", $request_id)->first();

    if($requestResult){
     
      $status = true;
      $message ="";
      $error = "";
      $data = array("service_type"=>$requestResult->service_type);
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }else{
      $status = false;
      $message ="";
      $error = "";
      $data = "Request not found";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
    }

  }  
  

  // fetch service provider by service type
  public function getServiceProvidersByServiceType(Request $request){
    $service_type = $request->service_type;
    $users = User::where("service_type", $service_type )->get();

     if($users){
     
      foreach($users as $user){
      
          $service_providers[] = array("id"=>$user->id, "name"=>$user->name);  
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



  // fetch service provider by  Tractor service type
  public function getServiceProvidersByTractor(Request $request){
    
    //get Tractor service type from table
     $service_type = ServiceType::where('id', '1')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 




  // fetch service provider by  Plower service type
  public function getServiceProvidersByPlower(Request $request){
    
    //get Plower service type from table
     $service_type = ServiceType::where('id', '2')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 



 
  // fetch service provider by  Planter service type
  public function getServiceProvidersByPlanter(Request $request){
    
    //get Planter service type from table
     $service_type = ServiceType::where('id', '3')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 




  // fetch service provider by  Seed service type
  public function getServiceProvidersBySeed(Request $request){
    
    //get Seed service type from table
     $service_type = ServiceType::where('id', '4')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 



  // fetch service provider by  Pesticide service type
  public function getServiceProvidersByPesticide(Request $request){
    
    //get Pesticide service type from table
     $service_type = ServiceType::where('id', '5')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 



   // fetch service provider by  Fertilizer service type
  public function getServiceProvidersByFertilizer(Request $request){
    
    //get Fertilizer service type from table
     $service_type = ServiceType::where('id', '6')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 



    // fetch service provider by  Processor service type
  public function getServiceProvidersByProcessor(Request $request){
    
    //get Processor service type from table
     $service_type = ServiceType::where('id', '7')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 



   // fetch service provider by  Havester service type
  public function getServiceProvidersByHarvester(Request $request){
    
    //get Harvester service type from table
     $service_type = ServiceType::where('id', '8')->first()->service;
   
    $users = User::where("service_type", $service_type )->get();

    if (!$users){
      $status = false;
      $message ="No service provider currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $users;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }

  } 

 
 // all farmer and agent request by location for his own only
 public function allFarmerAgentRequestByLocation(Request $request){
  $user_id = Auth::user()->id;
  $profile = UserProfile::where(['user_id' => $user_id])->first();
  $location = $profile->location;
  $all_request = OrderRequest::where("location", $location)->where('sp_id', $user_id)->where('status','!=','remove')->get();
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
      'description' => 'required'
  

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
      $product->user_id = Auth::user()->id;
      $product->prod_status="pending";
      $product->save();

      $status = true;
      $message ="You have successfully created ".$request->name." as a product";
      $error = "";
      $data = "";
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
  
  } 
}
  //fetch all product by service
  public function allProductsByServiceProvider(Request $request){
    $sp_id  = $request->sp_id;
    $all_products  = ServiceProduct::where("user_id", $sp_id)->get();
    if($all_products){
      $status = true;
      $message ="";
      $error = "";
      $data = $all_products;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="No product currently available";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }


  }  

  //fetch all product
  public function allProducts(Request $request){
    $all_products  = ServiceProduct::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_products;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }   




  public function getFarmRequest(Request $request){
    $user_id = Auth::user()->id;

    $all_request = OrderRequest::where('sp_id', $user_id)->get();

     if($all_request){
      $status = true;
      $message ="";
      $error = "";
      $data = $all_request;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="No farm request available";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }

  }


  public function getAgentPayment(Request $request){
      $user_id = Auth::user()->id;

    $payment = OrderRequest::Join('users', 'users.id', '=', 'request.sp_id')
                  ->Join('payment', 'payment.request_id', '=', 'request.id')
                  ->get(['payment.*', 'request.*']);

     if($payment){
      $status = true;
      $message ="";
      $error = "";
      $data = $payment;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="No payment available";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }

  }




  public function acceptRequest(Request $request){

      // validation
    $validator =Validator ::make($request->all(), [
      'request_id' => 'required'
  ]);      
  if($validator->fails()){
  $status = false;
  $message ="";
  $error = $validator->errors()->first();
  $data = "";
  $code = 401;                
  return ResponseBuilder::result($status, $message, $error, $data, $code);   
  }else{
    $user_id = Auth::user()->id;

    $request_id = $request->input('request_id');

    $requestResult  = OrderRequest::where('id',$request_id)->first();
    if($requestResult){

      

      $requestResult  = OrderRequest::where('id',$request_id)
                         ->where('sp_id', $user_id)
                        ->update([

                      'status' => 'Service Provider Accepted'

                    ]);

      $status = true;
      $message ="";
      $error = "";
      $data = $requestResult;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="Something went wrong";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }
  }

}



  public function rejectRequest(Request $request){

      // validation
    $validator =Validator ::make($request->all(), [
      'request_id' => 'required'
  ]);      
  if($validator->fails()){
  $status = false;
  $message ="";
  $error = $validator->errors()->first();
  $data = "";
  $code = 401;                
  return ResponseBuilder::result($status, $message, $error, $data, $code);   
  }else{
    $user_id = Auth::user()->id;

    $request_id = $request->input('request_id');

    $requestResult  = OrderRequest::where('id',$request_id)->first();
    if($requestResult){

      

      $requestResult  = OrderRequest::where('id',$request_id)
                         ->where('sp_id', $user_id)
                        ->update([

                      'status' => 'Service Provider Declined'

                    ]);

      $status = true;
      $message ="";
      $error = "";
      $data = $requestResult;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="Something went wrong";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }
  }

}



  public function startService(Request $request){

      // validation
    $validator =Validator ::make($request->all(), [
      'request_id' => 'required'
  ]);      
  if($validator->fails()){
  $status = false;
  $message ="";
  $error = $validator->errors()->first();
  $data = "";
  $code = 401;                
  return ResponseBuilder::result($status, $message, $error, $data, $code);   
  }else{
    $user_id = Auth::user()->id;

    $request_id = $request->input('request_id');

    $requestResult  = OrderRequest::where('id',$request_id)->first();
    if($requestResult){

      

      $requestResult  = OrderRequest::where('id',$request_id)
                         ->where('sp_id', $user_id)
                        ->update([

                      'status' => 'Service in progress'

                    ]);

      $status = true;
      $message ="";
      $error = "";
      $data = 'Service in progress';
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="Something went wrong";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }
  }

}


  public function endService(Request $request){

      // validation
    $validator =Validator ::make($request->all(), [
      'request_id' => 'required'
  ]);      
  if($validator->fails()){
  $status = false;
  $message ="";
  $error = $validator->errors()->first();
  $data = "";
  $code = 401;                
  return ResponseBuilder::result($status, $message, $error, $data, $code);   
  }else{
    $user_id = Auth::user()->id;

    $request_id = $request->input('request_id');

    $requestResult  = OrderRequest::where('id',$request_id)->first();
    if($requestResult){

      

      $requestResult  = OrderRequest::where('id',$request_id)
                         ->where('sp_id', $user_id)
                        ->update([

                      'status' => 'Service Delivered'

                    ]);

      $status = true;
      $message ="";
      $error = "";
      $data = 'Delivered';
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }else{
      $status = false;
      $message ="Something went wrong";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }
  }

}


}//class