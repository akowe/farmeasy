<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Role extends Model
{
    //
    protected $table = 'role';

    function get_role($user_type){
        $getRole= DB::table($this->table)->where('user_type', $user_type)->first();
        return $getRole->role;
    }
}
