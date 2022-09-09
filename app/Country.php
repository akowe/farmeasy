<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Country extends Model
{
    //
     protected $table ='country';

     function get_country_code($country){
        $getCountryCode= DB::table($this->table)->where('country', $country)->first();
        if($getCountryCode){
            return $getCountryCode->country_code;
        }else{
            return response()->json(["message"=>"This application is not allowed in ".ucfirst($country)]);
        }
        
    }
}
