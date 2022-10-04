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

        $payment = new Payment();
        $payment->request_id  = $request_id;
        $payment->ref         = $reference;
        $payment->pay_status  = $pay_status;
        $payment->gateway_ref = $gateway_ref;
        $payment->pay_date    = $paid_date;
        $payment->amount      = $amount;
        $payment->agent_email = $agent_email;
        $payment->agent_phone = $agent_phone;
        $payment->save();
  
        $status = true;
        $message ="successfully";
        $error = "";
        $data =  $better_response;
        $code = 200;                
        return ResponseBuilder::result($status, $message, $error, $data, $code); 
    
     // } 

  
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
