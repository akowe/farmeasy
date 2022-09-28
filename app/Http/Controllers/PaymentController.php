<?php

namespace App\Http\Controllers;
use App\Payment;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;

class PaymentController extends Controller
{

  //fetch all product
  public function payment(){
    
    // validation
    $validator =Validator ::make($request->all(), [
        'request_id' => 'required',
        'pay_reference' => 'required',
        'pay_status' => 'required',
        'gateway_ref' => 'required',
        'pay_date' => 'required',
        'amount' => 'required|numeric'
    ]);      
    if($validator->fails()){
    $status = false;
    $message ="";
    $error = $validator->errors()->first();
    $data = "";
    $code = 401;                
    return ResponseBuilder::result($status, $message, $error, $data, $code);   
    }else{

        $payment = new Payment();
        $payment->request_id= $request->request_id;
        $payment->pay_reference = $request->pay_reference;
        $payment->pay_status = $request->pay_status;
        $payment->gateway_ref = $request->gateway_ref;
        $payment->pay_date = $request->pay_date;
        $payment->amount = $request->amount;
        $payment->save();
  
        $status = true;
        $message ="You have successfully made payment";
        $error = "";
        $data = "";
        $code = 200;                
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
