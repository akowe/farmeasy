<?php 
 namespace App\Http\Helper;

 class ResponseBuilder
 {
    public static function result($status='',  $message='', $error='', $data='', $code=''){
       return [
        "success" => $status,
        "message" =>$message,
        'error' => $error,
        "data" => $data,
        "code" =>$code,
       ];
    }
 }