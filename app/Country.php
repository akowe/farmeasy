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
            return $getCountryCode;
        }else{
           return "false";        
            
        }
        
    }
}
