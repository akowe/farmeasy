<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Redirect;
use App\OrderRequest;
use Paystack;
use App\Payment;
use App\Price;

class PaymentController extends Controller
{
  
  public function payment($request_id){
      // validation
      //  $validator =Validator ::make($request->all(), [
      //   'request_id' => 'required'
      //   ]);      
      //   if($validator->fails()){
      //   $status = false;
      //   $message ="";
      //   $error = $validator->errors()->first();
      //   $data = "";
      //   $code = 401;                
      //   return ResponseBuilder::result($status, $message, $error, $data, $code);   
      //   }
      // else{ 
      //$request_id = $request->request_id;

      //get reference from request table
      $requestRef = OrderRequest::where('id',$request_id)->first();
      $requestRef->reference;
    
      $reference = $requestRef->reference;

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

    // get agent details that make payment
    $agent_email  = $better_response->data->customer->email;
    $agent_phone  = $better_response->data->metadata->phone;
    $amount       = $better_response->data->amount;
    $paid_date    = $better_response->data->created_at;
    $pay_status   = $better_response->data->gateway_response;
    $gateway_ref  = $better_response->data->reference;

//insert payment details to table
   $pay =  new Payment();
   $pay->request_id     = $request_id;
   $pay->agent_email    = $agent_email;
   $pay->agent_phone    = $agent_phone;
   $pay->ref            = $reference;
   $pay->pay_status     = $pay_status;
   $pay->gateway_ref    = $gateway_ref;
   $pay->pay_date       = $paid_date;
   $pay->amount         = $amount;

   $pay->save();

    //update request status to PAID
    if($pay_status == 'success'){
      OrderRequest::where('id',$request_id)
            ->update([
             'pay_status' => 'Paid'
            ]);
    }

    if($pay_status != 'success'){
       OrderRequest::where('id',$request_id)
            ->update([
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
  public function allPayments(){
    
    if(Gate::allows('view', $user)){
      $all_payments = Payment::all();
      $status = true;
      $message ="";
      $error = "";
      $data = $all_payments;
      $code = 200;                
      return ResponseBuilder::result($status, $message, $error, $data, $code); 
   }else{
    $status = false;
    $message ="Not Authorized to view all payment transactions";
    $error = "";
    $data = "";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);
   }    

  }



}
