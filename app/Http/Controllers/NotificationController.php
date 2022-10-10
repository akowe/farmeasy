<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AgentNotification;
use App\FarmerNotification;
use App\ServiceNotification;
use App\User;
use App\FeedBack;
use App\Otp;
use App\OrderRequest;
use Carbon\Carbon;
use App\UserProfile;

use Illuminate\Support\Facades\Auth;
use App\Http\Helper\ResponseBuilder;
class NotificationController extends Controller
{
// Agent mark notification as READ
public function getAgentNotification(Request $request){
  
    if( Auth::user()->user_type == '3'){
      $user_id = Auth::user()->id;
       $profile = UserProfile::where(['user_id' => $user_id])->first();

      $location = $profile->location;
      
       
      $notification = AgentNotification::where('id',$request->id)
                      ->where('location', $location)
                      ->whereNull('read_at')->first();
      if($notification){
  
        $notificationResult = AgentNotification::where('id',$request->id)
        ->update([
  
          'id'        => $request->id,
          'agent_id'  => $user_id,
          'read_at' => date('Y-m-d h:i:s')
  
        ]);
  
        $status = true;
        $message ="";
        $error = "";
        $data = 'Read';
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }else{
        $status = false;
        $message ="";
        $error = "";
        $data = "This notification has been read";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);     
      }
    }else{
      $status = false;
        $message ="You don't have permission to view this page";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
  
  }


// Agent view all UNREAD notification
public function getAgentNotifications(){

    if( Auth::user()->user_type == '3'){
      $user_id = Auth::user()->id;
       $profile = UserProfile::where(['user_id' => $user_id])->first();

      $location = $profile->location;
  
      $notifications = AgentNotification::whereNull('read_at')
                        ->where('location', $location)
                        ->get();
      $status = true;
      $message ="";
      $error = "";
      $data = $notifications;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    }
    else{
      $status = false;
        $message ="You don't have permission to view this page";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
  
  }




  // FARMER view all UNREAD notification
public function getFarmerNotifications(){

    if( Auth::user()->user_type == '4'){
      $user_id = Auth::user()->id;
       $profile = UserProfile::where(['user_id' => $user_id])->first();

      $location = $profile->location;
  
      $notifications = FarmerNotification::whereNull('read_at')
                        ->where('farmer_id', $user_id)
                        ->get();
      $status = true;
      $message ="";
      $error = "";
      $data = $notifications;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    }
    else{
      $status = false;
        $message ="You don't have permission to view this page";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
  
  }



  // Farmer mark notification as READ
public function getFarmerNotification(Request $request){
  
    if( Auth::user()->user_type == '4'){
      $user_id = Auth::user()->id;

      $notification = FarmerNotification::where('id',$request->id)
                      ->where('farmer_id', $user_id)
                      ->whereNull('read_at')->first();
      if($notification){
  
        $notificationResult = FarmerNotification::where('id',$request->id)
        ->update([
  
          'id'        => $request->id,
          'read_at' => date('Y-m-d h:i:s')
  
        ]);
  
        $status = true;
        $message ="";
        $error = "";
        $data = 'Read';
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }else{
        $status = false;
        $message ="";
        $error = "";
        $data = "This notification has been read";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);     
      }
    }else{
      $status = false;
        $message ="You don't have permission to view this page";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
  
  }

  



   // SERVICE PROVIDER view all UNREAD notification
public function getServiceNotifications(){

    if( Auth::user()->user_type == '5'){
      $user_id = Auth::user()->id;
       $profile = UserProfile::where(['user_id' => $user_id])->first();

      $location = $profile->location;
  
      $notifications = ServiceNotification::whereNull('read_at')
                        ->where('sp_id', $user_id)
                        ->get();
      $status = true;
      $message ="";
      $error = "";
      $data = $notifications;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    }
    else{
      $status = false;
        $message ="You don't have permission to view this page";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
  
  }



  // SERVICE PROVIDER mark notification as READ
public function getServiceNotification(Request $request){
  
    if( Auth::user()->user_type == '5'){
      $user_id = Auth::user()->id;

      $notification = ServiceNotification::where('id',$request->id)
                      ->where('sp_id', $user_id)
                      ->whereNull('read_at')->first();
      if($notification){
  
        $notificationResult = ServiceNotification::where('id',$request->id)
        ->update([
  
          'id'        => $request->id,
          'read_at' => date('Y-m-d h:i:s')
  
        ]);
  
        $status = true;
        $message ="";
        $error = "";
        $data = 'Read';
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }else{
        $status = false;
        $message ="";
        $error = "";
        $data = "This notification has been read";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);     
      }
    }else{
      $status = false;
        $message ="You don't have permission to view this page";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }
  
  }


}
