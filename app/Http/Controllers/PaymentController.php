<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;
use App\User;
use App\FeedBack;
use App\Otp;
use App\OrderRequest;
use Carbon\Carbon;
use App\UserProfile;
use Carbon\Profile;
use App\ServiceType;
use App\Payment;
use App\Price;
use App\AgentNotification;
use App\FarmerNotification;
use App\ServiceNotification;

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

class PaymentController extends Controller
{
  
  public function payment(Request $request){

      $request_id = $request['request_id'];
       $reference = $request['reference'];

      $crl = curl_init('https://api.paystack.co/transaction/verify/'.$reference);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLINFO_HEADER_OUT, true);
    
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
          "authorization: Bearer sk_test_8fabc18c29f908e5b7540b54d38a4b097250c39b", //replace this with your own test key
          "content-type: application/json",
          "cache-control: no-cache")
      );

    $response = curl_exec($crl);
    curl_close($crl);

    $better_response = json_decode($response);

    // get payment details
    $amount       = $better_response->data->amount;
    $paid_date    = $better_response->data->created_at;
    $pay_status   = $better_response->data->gateway_response;
    $gateway_ref  = $better_response->data->reference;

//insert payment details to table
   $pay =  new Payment();
   $pay->request_id     = $request_id;
   $pay->ref            = $reference;
   $pay->pay_status     = $pay_status;
   $pay->gateway_ref    = $gateway_ref;
   $pay->pay_date       = $paid_date;
   $pay->amount         = $amount;

   $pay->save();

    //update request status to PAID
    if($pay_status == 'Successful'){
      OrderRequest::where('id',$request_id)
            ->update([
              'agent_id' => Auth::user()->id,
             'pay_status' => 'Paid'
            ]);
    }

    if($pay_status != 'Successful'){
       OrderRequest::where('id',$request_id)
            ->update([
            'agent_id' => Auth::user()->id,
             'pay_status' => 'Unpaid'
            ]);
    }

    if($pay){
      $status = true;
      $message ="";
      $error = "";
      $data = $better_response;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
    }
    else{
       $status = false;
    $message ="Opps! Something went wrong.";
    $error = "";
    $data = "";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);
    }


  
}


  

  //fetch all payments
  public function allPayments(User $user){

      $all_payments = Payment::all();
      $status = true;
      $message ="";
      $error = "";
      $data = $all_payments;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }



}
