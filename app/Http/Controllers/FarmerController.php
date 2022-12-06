<?php

namespace App\Http\Controllers;

//phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestMail;
use App\User;
use App\UserProfile;
use App\Otp;
use App\Role;
use App\Country;
use App\FarmType;
use App\AgentNotification;
use App\FarmerNotification;
use App\ServiceNotification;
use Carbon\Carbon;
use Carbon\Profile;
use App\OrderRequest;
use App\ServiceType;
use App\BecomeAgent;
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



class FarmerController extends Controller
{
  

    public function __construct()
    {
    
    }

    public function createFarmer(Request $request){
             // validation
          $validator =Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|numeric|unique:users,phone',
            'farm_type' => 'required',
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
              
                $user_type = 'farmer';
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
                $user->user_type   =  '4'; // can select from role table
                $user->farm_type   = $request['farm_type']; //select fron db 'service' 
                $user->password    = Hash::make($request['password']);
                $user->status      = 'pending';
                
                $user->save();    

                if($user){
                   // upon successful registration create profile for user so user can edit their profile later
                 
                 
                  $profile = new UserProfile();
                  $profile->user_id = $user->id;
                  $profile->farm_type = $user->farm_type;
                  $profile->save();

                  //implemented sms
                  $country_code = $country->get_country_code($request['country']);
                 // https://api.ebulksms.com:4433/sendsms.json
                  //http://api.ebulksms.com:8080/sendsms.json
                  $json_url = "https://api.ebulksms.com:4433/sendsms.json";
                  $username = '';
                  $apikey = '';
      
                  $sendername = 'FarmEASY';
                  $messagetext = 'Kindly use '.$reg_code.' to verify your account on FarmEASY App';
      
                  
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
  
  // fetch all farm types
  public function allFarmTypes(){
 
    $all_farm_types  = FarmType::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



    // fetch Rice Farm type
  public function RiceFarm(){
 
    $all_farm_types  = Rice_farm_type::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 

   public function WheatFarm(){
 
    $all_farm_types  = Wheat_farm_type::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 


   public function MaizeFarm(){
 
    $all_farm_types  = Maize_farm_type::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_farm_types;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  } 



    //farmer request for tractor
    public function requestTractor(Request $request){
      
      // validation
      $validator =Validator ::make($request->all(), [

        'service_type' => 'required',
        'amount' => 'required',
        'sp_id' => 'required',
        'name' => 'required',
        'phone' => 'required',
        'location' => 'required',
        'measurement' => 'required'
      

   ]);      
    if($validator->fails()){
     $status = false;
     $message ="";
     $error = $validator->errors()->first();
     $data = "";
     $code = 401;                
     return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }else{
     
        // $profile = UserProfile::where(function($q){
        //   return $q->whereNull("profile_update_at");
        // })->first();
        // if($profile){
          $amount = $request->measurement * $request->amount;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id = Auth::user()->id;
          $orderRequest->name = $request->name;
          $orderRequest->phone = $request->phone;
          $orderRequest->amount = $amount;
          $orderRequest->land_hectare = $request->measurement;
          $orderRequest->location = $request->location;
          $orderRequest->service_type =$request->service_type;// this should be select fromdropdown
          $orderRequest->sp_id =$request->sp_id; //this should be selest from dropdown
          $orderRequest->status = "pending";
          $orderRequest->save();
          $status = true;
          $message =Ucwords($request->name)." your request for ".$request->service_type." is successful";
          $error = "";
          $data = "";
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);            
             
    }    

}

//farmer click to request Tractor service
public function HireTractor(Request $request){

      $username   =  Auth::user()->name;
      $user_id    =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;

      //get login user location from profile table
      $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Tractor service type from table
      $tractor = ServiceType::where('id', '1')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id  = $user_id;
          $orderRequest->name     = $username;
          $orderRequest->phone    = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type = $tractor;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status   = "pending";
          $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;
              $agentLocation = $profile->location;          
            }
          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = " ";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($tractor);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($tractor)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($tractor)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($tractor)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($tractor)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($tractor)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email

              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($tractor)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
      }    
  }



//farmer click to request Plower service
public function HirePlough(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
      $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Plower service type from table
      $plower = ServiceType::where('id', '2')->first()->service;

        if (!$location){
      $status = false;
      $message ="Kindly update your profile before requesting a service";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
        //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$plower;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;  
              $agentLocation = $profile->location;          
            }

          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($plower);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($plower)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($plower)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($plower)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($plower)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($plower)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email

          $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($plower)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
      }
    }   


  //farmer click to request Planter service
public function HirePlanter(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Planter service type from table
      $planter = ServiceType::where('id', '3')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
        //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$planter;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;   
              $agentLocation = $profile->location;         
            }
          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = " ";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($planter);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($planter)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($planter)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($planter)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($planter)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($planter)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
           $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($planter)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
      }
}


//farmer click to request Seed service
public function HireSeed(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Seed service type from table
      $seed = ServiceType::where('id', '4')->first()->service;

      if(!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
        //$location = $location ->location;
        $orderRequest = new OrderRequest();
        $orderRequest->user_id =$user_id;
        $orderRequest->name = $username;
        $orderRequest->phone = $user_phone;
        $orderRequest->location = $location;
        $orderRequest->service_type =$seed;
        $orderRequest->farm_type = $farm_type;
        $orderRequest->status = "pending";
        $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;  
              $agentLocation = $profile->location;          
            }
          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($seed);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($seed)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($seed)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($seed)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($seed)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($seed)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($seed)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
}


//farmer click to request Pesticide service
public function HirePesticide(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
      $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Pesticide service type from table
      $pesticide = ServiceType::where('id', '5')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$pesticide;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;   
              $agentLocation = $profile->location;         
            }
          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = " ";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($pesticide);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($pesticide)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($pesticide)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($pesticide)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($pesticide)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($pesticide)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($pesticide)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
}




  //farmer click to request Fertilizer service
public function HireFertilizer(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Fertilizer service type from table
      $fertilizer = ServiceType::where('id', '6')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$fertilizer;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;  
              $agentLocation = $profile->location;          
            }
          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($fertilizer);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($fertilizer)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($fertilizer)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($fertilizer)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($fertilizer)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($fertilizer)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
       $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($fertilizer)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }   
}


//farmer click to request Harrow service
public function HireHarrow(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
      $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
      $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Pesticide service type from table
      $harrow = ServiceType::where('id', '7')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$harrow;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();

          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email;   
              $agentLocation = $profile->location;         
            }
          }

          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = " ";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($harrow);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($harrow)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($harrow)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($harrow)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($harrow)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($harrow)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($harrow)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
}



          //farmer click to request Harvester service
  public function HireHarvester(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
       $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Harvester service type from table
      $harvester = ServiceType::where('id', '8')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$harvester;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();
          
          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email; 
              $agentLocation = $profile->location;           
            }
          }

         
          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($harvester);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($harvester)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($harvester)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($harvester)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($harvester)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($harvester)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($harvester)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
     }    
  } 




          //farmer click to request Harvester service
  public function HireRidger(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
       $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Farm  Ridger service type from table
      $ridger = ServiceType::where('id', '9')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$ridger;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();
          
          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email; 
              $agentLocation = $profile->location;           
            }
          }

         
          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($ridger);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($ridger)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($ridger)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($ridger)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($ridger)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($ridger)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($ridger)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
     }    
  } 




          //farmer click to request Boom service
  public function HireBoom(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
       $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Farm  Ridger service type from table
      $boom = ServiceType::where('id', '10')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$boom;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();
          
          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email; 
              $agentLocation = $profile->location;           
            }
          }

         
          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($boom);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($boom)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($boom)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($boom)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($boom)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($boom)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($boom)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
     }    
  } 



          //farmer click to request Extension service
  public function HireExtension(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
       $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Farm  Ridger service type from table
      $ext = ServiceType::where('id', '11')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$ext;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();
          
          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email; 
              $agentLocation = $profile->location;           
            }
          }

         
          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($ext);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($ext)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($ext)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($ext)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($ext)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($ext)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($ext)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
     }    
  } 



 //farmer click to request Off Taker service
  public function HireOfftaker(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
       $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Farm  Ridger service type from table
      $offtaker = ServiceType::where('id', '12')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$offtaker;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();
          
          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email; 
              $agentLocation = $profile->location;           
            }
          }

         
          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($offtaker);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($offtaker)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($offtaker)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully informed the ".strtolower($offtaker)." . You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($offtaker)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully informed  the ".strtolower($offtaker)." . You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully informed the ".strtolower($offtaker)." . You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
     }    
  } 


//farmer click to request Treasher service
  public function HireTreasher(Request $request){

      $username = Auth::user()->name;
      $user_id =  Auth::user()->id;
      $user_phone = Auth::user()->phone;
       $farm_type  = Auth::user()->farm_type;
 
      //get login user location from profile table
     $location = UserProfile::where('user_id', $user_id)->first()->location;

      //get Farm  Ridger service type from table
      $treasher = ServiceType::where('id', '13')->first()->service;

      if (!$location){
        $status = false;
        $message ="Kindly update your profile before requesting a service";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
      }else{
          //$location = $location ->location;
          $orderRequest = new OrderRequest();
          $orderRequest->user_id =$user_id;
          $orderRequest->name = $username;
          $orderRequest->phone = $user_phone;
          $orderRequest->location = $location;
          $orderRequest->service_type =$treasher;
          $orderRequest->farm_type = $farm_type;
          $orderRequest->status = "pending";
          $orderRequest->save();
          
          //notification
          //get agents in the same location with the farmer
          $agentEmails = array();
          $agents = User::where('user_type','3')->get();

          foreach($agents as $agent){
            $profile= UserProfile::where('user_id',$agent->id)->first();
            if($location ==$profile->location){
              $agentEmails[]=   $profile->email; 
              $agentLocation = $profile->location;           
            }
          }

         
          // send email
          if(count($agentEmails) !=0){
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
      
            $mail = new PHPMailer(true);
  
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
            $mail->isSMTP();                                            
            $mail->SMTPDebug = 0; 
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl'; //'ssl';
            $mail->Host = "smtp.gmail.com";                                                   
            $mail->Username = "";
            $mail->Password = "";                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465; //465;                                   
            //Recipients
            $mail->setFrom("noreply@fme.com", 'FARM EASY');
            foreach($agentEmails as $email){
              //if email is not valid or empty
              if($email){
                $mail->addAddress($email);  
                $mail->isHTML(true);                                 
                $mail->Subject = "Order request for a ".strtolower($treasher);
                $mail->MsgHTML("<p>".ucfirst($username)." in your location just made a request for a ".strtolower($treasher)."</p>"); 
            if($mail->send()){
              $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($treasher)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($treasher)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code); 
              }   

            }
            else{
             $notification= new AgentNotification();
              $notification->request_id  =$orderRequest->id;
              $notification->location    = $agentLocation;
              $notification->type        = "request";
              $notification->description = "You have a new ".strtolower($treasher)." hire request";
              $notification->notice_status = "delivered"; 
              $notification->save();       
              $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($treasher)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);                  
            } 
            
            }// get email
          }//end send email
        $status = true;
              $message =Ucwords($username).", you have successfully requested for the ".strtolower($treasher)."  service. You will be contacted shortly.";
              $error = "";
              $data = "Request successful";
              $code = 200;                
              return ResponseBuilder::result($status, $message, $error, $data, $code);  
     }    
  } 


       // Farmer View all his farm request status
  public function FarmerRequestHistory(Request $request){
    $user_id =  Auth::user()->id;
    $all_request = OrderRequest::where("user_id", $user_id)->get();
  
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


  public function getFarmers(){
    //get all farmers 
    $user_farmers =  User::Join('profile','profile.user_id', '=', 'users.id')
                  ->where('users.user_type', '4')
                  ->get(['users.*', 'profile.*']);

    if (!$user_farmers ){
      $status = false;
      $message ="No Farmer currently avaliable";
      $error = "";
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);  
    }
      
     else{
        $status = true;
      $message ="";
      $error = "";
      $data = $user_farmers;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);    
     }  
}   



}
