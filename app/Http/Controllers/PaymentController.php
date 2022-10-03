<?php

namespace App\Http\Controllers;
use App\Payment;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;

class PaymentController extends Controller
{


  public function genReference($qtd){
   
    $Caracteres = 'ABCDEFGHIJKLMOPQRSTUVXWYZ0123456789';
    $QuantidadeCaracteres = strlen($Caracteres);
    $QuantidadeCaracteres--;

    $Hash=NULL;

    for($x=1;$x<=$qtd;$x++){
        $Posicao = rand(0,$QuantidadeCaracteres);
        $Hash .= substr($Caracteres,$Posicao,1);
    }

    return $Hash;
}

  //fetch all product
  public function payment(){
    $requestResult = OrderRequest::where('id',$request->request_id)->first();
            
    $curl = curl_init();

    $user_id = Auth::user()->id;
    $profileResult = UserProfile::where('id',$user_id)->first();
    $email = $profileResult->email;
    $amount = $$requestResult->measurement * $requestResult->price; 
    
    // url to go to after payment
    //$callback_url = site_url().'/verify';
   // echo $callback_url;
    //exit;

    
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode([
        'amount'=>$amount,
        'email'=>$email,
        //'callback_url' => $callback_url
      ]),
      CURLOPT_HTTPHEADER => [
        "authorization: Bearer sk_test_8fabc18c29f908e5b7540b54d38a4b097250c39b", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
      ],
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    if($err){
      // there was an error contacting the Paystack API
      $status = false;
      $message ="";
      $error = $err;
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);
    }
    
    $tranx = json_decode($response, true);
    
    if(!$tranx['status']){
      // there was an error from the API
     // print_r('API returned error: ' . $tranx['message']);
      $status = false;
      $message ="";
      $error = $tranx['message'];
      $data = "";
      $code = 401;                
      return ResponseBuilder::result($status, $message, $error, $data, $code);     
    }    
    var_dump($tranx['status']);
    exit;
        $payment = new Payment();
        $payment->request_id= $request->request_id;
        $payment->pay_reference = $this->genReference(6);
        $payment->pay_status = "paid";
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
