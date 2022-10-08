<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notification;
use Illuminate\Support\Facades\Auth;
use App\Http\Helper\ResponseBuilder;
class NotificationController extends Controller
{
// this should be for agent only
public function getNotification(Request $request){
  
    if( Auth::user()->user_type == '3'){
       
      $notification = Notification::where('id',$request->id)->whereNull('read_at')->first();
      if($notification){
  
        $notificationResult = Notification::where('id',$request->id)
        ->update([
  
          'id' => $request->id,
          'read_at' => date('Y-m-d h:i:s')
  
        ]);
  
        $status = true;
        $message ="";
        $error = "";
        $data = $notification;
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }else{
        $status = false;
        $message ="";
        $error = "";
        $data = "read";
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


// this should be for agent only
public function getNotifications(){

    if( Auth::user()->user_type == '3'){
  
      $notifications = Notification::whereNull('read_at')->get();
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
  

}
