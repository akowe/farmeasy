<?php

namespace App\Http\Controllers;
use App\Payment;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Redirect;
use App\OrderRequest;
use Paystack;

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

<<<<<<< HEAD
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
    $agent_email = $better_response->data->customer->email;
    $agent_phone = $better_response->data->metadata->phone;
    $amount = $better_response->data->amount;
    $paid_date = $better_response->data->created_at;
    $pay_status = $better_response->data->gateway_response;
    $gateway_ref = $better_response->data->reference;

    //update request status to PAID
    if($pay_status == 'successfully'){
      OrderRequest::where('id',$request_id)
            ->update([
             'pay_status' => 'Paid'
            ]);
    }

    if($pay_status != 'successfully'){
       OrderRequest::where('id',$request_id)
            ->update([
             'pay_status' => 'Unpaid'
            ]);
    }
=======

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
>>>>>>> 1f4e52118d9e2dcdecd1428954a93a1b43ca4dae

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
<<<<<<< HEAD
        $payment->request_id  = $request_id;
        $payment->ref         = $reference;
        $payment->pay_status  = $pay_status;
        $payment->gateway_ref = $gateway_ref;
        $payment->pay_date    = $paid_date;
        $payment->amount      = $amount;
        $payment->agent_email = $agent_email;
        $payment->agent_phone = $agent_phone;
=======
        $payment->request_id= $request->request_id;
        $payment->pay_reference = $this->genReference(6);
        $payment->pay_status = "paid";
        $payment->gateway_ref = $request->gateway_ref;
        $payment->pay_date = $request->pay_date;
        $payment->amount = $request->amount;
>>>>>>> 1f4e52118d9e2dcdecd1428954a93a1b43ca4dae
        $payment->save();
  
        $status = true;
        $message ="successfully";
        $error = "";
        $data =  $better_response;
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
<<<<<<< HEAD
    
     // } 
=======
>>>>>>> 1f4e52118d9e2dcdecd1428954a93a1b43ca4dae

  
  }

<<<<<<< HEAD



=======
>>>>>>> 1f4e52118d9e2dcdecd1428954a93a1b43ca4dae
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


  /**
     * Redirect the User to Paystack Payment Page
     * @return Url
     */
    public function redirectToGateway()
    {
        try{
            return Paystack::getAuthorizationUrl()->redirectNow();
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }        
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();

        dd($paymentDetails);
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    } 


}
