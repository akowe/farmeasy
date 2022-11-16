<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
use App\AgentNotification;
use App\FarmerNotification;
use App\ServiceNotification;
use App\Payment;
use App\Price;

use App\Rice_farm_type;
use App\Wheat_farm_type;
use App\Maize_farm_type;

use App\Boom_sprayer_service;
use App\Extension_service;
use App\Fertilizer_service;
use App\Harrow_service;
use App\Harvester_service;
use App\Off_taker_service;
use App\Pesticide_herbicide_service;
use App\Planter_service;
use App\Plough_service;
use App\Ridger_service;
use App\Seeds_service;
use App\Tractor_service;
use App\Treasher;

use Illuminate\Support\Arr;


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
                
                if($user){

                  
                // upon successful registration create profile for user so user can edit their profile later

                  $profile            = new UserProfile();
                  $profile->user_id   = $user->id;
                  $profile->service_type = $user->service_type;
                  $profile->save();

             //implemented sms
                  $country_code = $country->get_country_code($request['country']);
                 // https://api.ebulksms.com:4433/sendsms.json
                  //http://api.ebulksms.com:8080/sendsms.json
                  $json_url = "https://api.ebulksms.com:4433/sendsms.json";
                  $username = 'admin@riceafrika.com';
                  $apikey = 'eda594a3b4f30a20857dd9a80fcde0ff69840cb7';
      
                  $sendername = 'FarmEASY';
                  $messagetext = 'Kindly use '.$reg_code.'  to verify your account on FarmEASY App';
      
                  
                  $gsm = array();

                  // remove the + sign from countrycode. ebulksms requiment for sending
                  $country_code = trim($country_code->country_code, "+");  

                  //remove first "0" from phone number             
                  $arr_recipient = explode(',', trim($request['phone'], "0"));
                  $phone =implode(',',$arr_recipient);

                  $generated_id = uniqid('int_', false);
                  $generated_id = substr($generated_id, 0, 30);
                  $gsm['gsm'][] = array('msidn' => $country_code.$phone, 'msgid' => $generated_id);
      
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
                    $message ="sms is not sent";
                    $error = '';
                    $data ="";
                    $code = 400;
                    return ResponseBuilder::result($status, $message, $error, $data, $code);
                  }else if($response){
                    $status = true;
                    $message ="sms sent successfully";
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
  public function service_providers_by_service_type(Request $request){
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
  

    $users =  User::where('service_type', 'like', '%tractor%')->get();    


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
   
       $users =  User::where('service_type', 'like', '%plough%')->get();    

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
   
    $users =  User::where('service_type', 'like', '%planter%')->get();  

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
   
   $users =  User::where('service_type', 'like', '%seed%')->get();  

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
   
    $users =  User::where('service_type', 'like', '%pesticide%')->get();  

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

    $users =  User::where('service_type', 'like', '%fertilizer%')->get();  

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



    // fetch service provider by  Harrow service type
  public function getServiceProvidersByHarrow(Request $request){
    
    //get Harrow service type from table
     $service_type = ServiceType::where('id', '7')->first()->service;
   
     $users =  User::where('service_type', 'like', '%harrow%')->get();  

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
   
    $users =  User::where('service_type', 'like', '%harvester%')->get();  

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


  // fetch service provider by  ridger  service type
  public function getServiceProvidersByridger(Request $request){
    
    //get Extension Manager service type from table
     $service_type = ServiceType::where('id', '9')->first()->service;
   
    $users =  User::where('service_type', 'like', '%ridger%')->get();  

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



  // fetch service provider by  Boom  service type
  public function getServiceProvidersByboom(Request $request){
    
    //get Extension Manager service type from table
     $service_type = ServiceType::where('id', '10')->first()->service;
   
   $users =  User::where('service_type', 'like', '%boom%')->get();  

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


    // fetch service provider by  Extension Manager service type
  public function getServiceProvidersByExtension(Request $request){
    
    //get Extension Manager service type from table
     $service_type = ServiceType::where('id', '11')->first()->service;
   
     $users =  User::where('service_type', 'like', '%extension%')->get();  

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


  // fetch service provider by  Extension Manager service type
  public function getServiceProvidersByOfftaker(Request $request){
    
    //get Extension Manager service type from table
     $service_type = ServiceType::where('id', '12')->first()->service;
   
     $users =  User::where('service_type', 'like', '%off taker%')->get();  

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




  // fetch service provider by  Extension Manager service type
  public function getServiceProvidersByTreasher(Request $request){
    
    //get Extension Manager service type from table
     $service_type = ServiceType::where('id', '13')->first()->service;
   
    $users =  User::where('service_type', 'like', '%treasher%')->get();  

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



 //count all vendors by service types
  public function countVendorsBYServiceTypes(){
 
    $tractor  = ServiceType::where('id', '1')->first()->service;
    $countTractor = User::where('service_type', 'like', '%tractor%')->count(); 

     //count plough
    $plough  = ServiceType::where('id', '2')->first()->service;
    $countPlough = User::where('service_type', 'like', '%plough%')->count(); 


    //count planter
    $planter  = ServiceType::where('id', '3')->first()->service;
    $countPlanter = User::where('service_type', 'like', '%planter%')->count(); 


      //count seed
    $seed  = ServiceType::where('id', '4')->first()->service;
    $countSeed = User::where('service_type', 'like', '%seed%')->count(); 


     //count pesticide
    $pesticide  = ServiceType::where('id', '5')->first()->service;
    $countPesticide = User::where('service_type', 'like', '%pesticide%')->count(); 


    //count Fertilizer
     $fertilizer  = ServiceType::where('id', '6')->first()->service;
    $countFertilizer = User::where('service_type', 'like', '%fertilizer%')->count(); 


    // count Harrow
    $harrow  = ServiceType::where('id', '7')->first()->service;
    $countHarrow = User::where('service_type', 'like', '%harrow%')->count(); 


    // count harvester
    $harvester  = ServiceType::where('id', '8')->first()->service;
    $countHarvester = User::where('service_type', 'like', '%harvester%')->count(); 


    //count ridger
     $ridger  = ServiceType::where('id', '9')->first()->service;
    $countRidger = User::where('service_type', 'like', '%ridger%')->count(); 


     //count Boom
     $boom  = ServiceType::where('id', '10')->first()->service;
    $countBoom = User::where('service_type', 'like', '%boom%')->count(); 


     //count Extension
     $extension  = ServiceType::where('id', '11')->first()->service;
    $countExtension = User::where('service_type', 'like', '%extension%')->count(); 


     //count Off taker
     $offtaker  = ServiceType::where('id', '12')->first()->service;
    $countOfftaker = User::where('service_type', 'like', '%off taker%')->count(); 


       //count treasher
     $treasher  = ServiceType::where('id', '13')->first()->service;
    $countTreasher = User::where('service_type', 'like', '%treasher%')->count(); 


     $data = array(
               'tractor' => array(
              'service_type' => $tractor,
              'vendors'=>$countTractor
              ),


              'plough' => array(
              'service_type' => $plough,
              'vendors'=>$countPlough
            ),


              'planter' => array(
              'service_type' => $planter,
              'vendors'=>$countPlanter
            ),


              'seed' => array(
              'service_type' => $seed,
              'vendors'=>$countSeed
            ),


              'pesticide' => array(
              'service_type' => $pesticide,
              'vendors'=>$countPesticide
            ),


              'fertilizer' => array(
              'service_type' => $fertilizer,
              'vendors'=>$countFertilizer
            ),
              


              'harrow' => array(
              'service_type' => $harrow,
              'vendors'=>$countHarrow
            ),
            

              'harvester' => array(
              'service_type' => $harvester,
              'vendors'=>$countHarvester
            ),
  
            
              'ridger' => array(
              'service_type' => $ridger,
              'vendors'=>$countRidger
            ),


              'boom' => array(
              'service_type' => $boom,
              'vendors'=>$countBoom
            ),


              'extension' => array(
              'service_type' => $extension,
              'vendors'=>$countExtension
            ),


              'offtaker' => array(
              'service_type' => $offtaker,
              'vendors'=>$countOfftaker
            ),


              'treasher' => array(
              'service_type' => $treasher,
              'vendors'=>$countTreasher
            ),


            );

    if($data){
    $status = true;
    $message ="";
    $error = "";
    $data = $data;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }else{
    $status = false;
    $message ="";
    $error = "";
    $data = "Something went wrong!";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }

  } 





 //fetch all sevrvice provider by service types
  public function FetchAllServiceProvider(){
 
    $tractor  = ServiceType::where('id', '1')->first()->service;
    $countTractor = User::where('service_type', 'like', '%tractor%')->get(); 

     //count plough
    $plough  = ServiceType::where('id', '2')->first()->service;
    $countPlough = User::where('service_type', 'like', '%plough%')->get(); 


    //count planter
    $planter  = ServiceType::where('id', '3')->first()->service;
    $countPlanter = User::where('service_type', 'like', '%planter%')->get(); 


      //count seed
    $seed  = ServiceType::where('id', '4')->first()->service;
    $countSeed = User::where('service_type', 'like', '%seed%')->get(); 


     //count pesticide
    $pesticide  = ServiceType::where('id', '5')->first()->service;
    $countPesticide = User::where('service_type', 'like', '%pesticide%')->get(); 


    //count Fertilizer
     $fertilizer  = ServiceType::where('id', '6')->first()->service;
    $countFertilizer = User::where('service_type', 'like', '%fertilizer%')->get(); 


    // count Harrow
    $harrow  = ServiceType::where('id', '7')->first()->service;
    $countHarrow = User::where('service_type', 'like', '%harrow%')->get(); 


    // count harvester
    $harvester  = ServiceType::where('id', '8')->first()->service;
    $countHarvester = User::where('service_type', 'like', '%harvester%')->get(); 


    //count ridger
     $ridger  = ServiceType::where('id', '9')->first()->service;
    $countRidger = User::where('service_type', 'like', '%ridger%')->get(); 


     //count Boom
     $boom  = ServiceType::where('id', '10')->first()->service;
    $countBoom = User::where('service_type', 'like', '%boom%')->get(); 


     //count Extension
     $extension  = ServiceType::where('id', '11')->first()->service;
    $countExtension = User::where('service_type', 'like', '%extension%')->get(); 


     //count Off taker
     $offtaker  = ServiceType::where('id', '12')->first()->service;
    $countOfftaker = User::where('service_type', 'like', '%off taker%')->get(); 


       //count treasher
     $treasher  = ServiceType::where('id', '13')->first()->service;
    $countTreasher = User::where('service_type', 'like', '%treasher%')->get(); 


     $data = array(
               'tractor' => array(
              'service_type' => $tractor,
              'user'=>$countTractor
              ),


              'plough' => array(
              'service_type' => $plough,
              'user'=>$countPlough
            ),


              'planter' => array(
              'service_type' => $planter,
              'user'=>$countPlanter
            ),


              'seed' => array(
              'service_type' => $seed,
              'user'=>$countSeed
            ),


              'pesticide' => array(
              'service_type' => $pesticide,
              'user'=>$countPesticide
            ),


              'fertilizer' => array(
              'service_type' => $fertilizer,
              'user'=>$countFertilizer
            ),
              


              'harrow' => array(
              'service_type' => $harrow,
              'user'=>$countHarrow
            ),
            

              'harvester' => array(
              'service_type' => $harvester,
              'user'=>$countHarvester
            ),
  
            
              'ridger' => array(
              'service_type' => $ridger,
              'user'=>$countRidger
            ),


              'boom' => array(
              'service_type' => $boom,
              'user'=>$countBoom
            ),


              'extension' => array(
              'service_type' => $extension,
              'user'=>$countExtension
            ),


              'offtaker' => array(
              'service_type' => $offtaker,
              'user'=>$countOfftaker
            ),


              'treasher' => array(
              'service_type' => $treasher,
              'user'=>$countTreasher
            ),


            );

    if($data){
    $status = true;
    $message ="";
    $error = "";
    $data = $data;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }else{
    $status = false;
    $message ="";
    $error = "";
    $data = "Something went wrong!";
    $code = 401;                
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



   public function TractorService(){
 
    $all_farm_types  = Tractor_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


    public function PloughService(){
 
    $all_farm_types  = Plough_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


    public function HarrowService(){
 
    $all_farm_types  = Harrow_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


      public function RidgerService(){
 
    $all_farm_types  = Ridger_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



    public function PlanterService(){
 
    $all_farm_types  = Planter_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



    public function BoomService(){
 
    $all_farm_types  = Boom_sprayer_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


      public function PesticideService(){
 
    $all_farm_types  = Pesticide_herbicide_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



      public function FertilizerService(){
 
    $all_farm_types  = Fertilizer_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 




 public function SeedService(){
 
    $all_farm_types  = Seeds_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



 public function ExtensionService(){
 
    $all_farm_types  = Extension_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


   public function OffTakerService(){
 
    $all_farm_types  = Off_taker_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


    public function HarvesterService(){
 
    $all_farm_types  = Harvester_service::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


    public function TreasherService(){
 
    $all_farm_types  = Treasher::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
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

      // the user name and phone is that of the agent so the service provider can contact agent
    $payment = OrderRequest::Join('users', 'users.id', '=', 'request.agent_id')
                  ->Join('payment', 'payment.request_id', '=', 'request.id')
                  ->where('request.sp_id', $user_id)
                  ->where('request.pay_status', 'Paid')
                  ->orderBy('pay_date', 'desc')
                  ->get(['payment.request_id', 'payment.id', 'payment.ref', 'payment.pay_date', 'payment.amount',  'payment.gateway_ref', 'payment.created_at',  
                    'request.pay_status', 'request.status', 'request.agent_id', 'request.hectare_rate', 'request.farm_size','request.location', 'request.service_type', 'request.name', 'users.name', 'users.phone' ]);



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

public function getServiceProviders(){
    //get service providers
    $users = User::Join('profile','profile.user_id', '=', 'users.id')
                  ->where('users.user_type', '5')
                  ->get(['users.*', 'profile.*']);

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

}//class