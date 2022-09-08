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
        return $getCountryCode->country_code;
    }
}
