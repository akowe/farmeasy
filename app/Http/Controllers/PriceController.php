<?php

namespace App\Http\Controllers;
use App\Price;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use App\Payment;

class PriceController extends Controller
{

    public function editPrice(Request $request, User $user){
      if(Gate::allows('edit', $user)){
        // validation
        $validator =Validator::make($request->all(), [
        'price_id' => 'required'
        ]);        
        if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 400;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
        } 
        $price_id = $request->price_id;
        $priceResult  = Price::where('id', $price_id)->first();  
        if($priceResult){
          $status = true;
          $message ="";
          $error = "";
          $data = $priceResult;
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);  


        }else{
            $status = false;
            $message ="Price request not found";
            $error = "";
            $data = "";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);  
        }
      }else{
        $status = false;
        $message ="Not Authorized to edit price";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }    
       
    }  
    public function updatePrice(Request $request, User $user){
      if(Gate::allows('update', $user)){
        // validation
        $validator =Validator::make($request->all(), [
        'price_id' => 'required',
        'price' => 'required|min:11|numeric',

        ]);        
        if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 400;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
        } 
        $price_id = $request->price_id;
        $priceResult  = Price::where('id', $price_id)->first();  
        if($priceResult){

            $priceResult = Price::where('id', $price_id)
            ->update([
            'price' =>$request->price
            ]);
            $status = true;
            $message ="You have successfully update the price";
            $error = "";
            $data = "";
            $code = 200;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);  


        }else{
            $status = false;
            $message ="Price request not found";
            $error = "";
            $data = "";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);  
        }
      }else{
        $status = false;
        $message ="Not Authorized to update price";
        $error = "";
        $data = "";
        $code = 401;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);
      }    
      
       
    }   
    public function getPriceByServiceType(Request $request){
        // validation
        $validator =Validator::make($request->all(), [
        'service_type_id' => 'required'
        ]);        
        if($validator->fails()){
        $status = false;
        $message ="";
        $error = $validator->errors()->first();
        $data = "";
        $code = 400;                
        return ResponseBuilder::result($status, $message, $error, $data, $code);   
        } 
        $service_type_id = $request->service_type_id;
        $priceResult  = Price::where('service_type_id', $service_type_id)->first();  
        if($priceResult){
          $status = true;
          $message ="";
          $error = "";
          $data = $priceResult;
          $code = 200;                
          return ResponseBuilder::result($status, $message, $error, $data, $code);  


        }else{
            $status = false;
            $message ="Price request not found";
            $error = "";
            $data = "";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);  
        }
       
    }
  //fetch all price
  public function allPrice(){

    $all_price  = Price::all();
    $status = true;
    $message ="";
    $error = "";
    $data = $all_price;
    $code = 200;                
    return ResponseBuilder::result($status, $message, $error, $data, $code); 

  }  
}
