<?php

namespace App\Policies;
use App\Price;
use App\User;

class PricePolicy
{
    public function update(User $user) {
        
       if($user->user_type === "1"){
            return true;
       }else if($user->user_type === "2"){
            return true;
       }
    }

    

}