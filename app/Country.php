<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Http\Helper\ResponseBuilder;

class Country extends Model
{
    //
     protected $table ='country';

     function get_country_code($country){
        $getCountryCode= DB::table($this->table)->where('country', $country)->first();
        if($getCountryCode){
            return $getCountryCode->country_code;
        }else{
            $status = false;
            $message ="This application is not allowed in".ucfirst($country);
            $error = '';
            $code = 400;     
            ResponseBuilder::result($status, $message, $error, $code);               
            
        }
        
    }
}
