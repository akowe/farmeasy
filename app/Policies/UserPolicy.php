<?php

namespace App\Policies;

use App\User;

class UserPolicy
{
    
    public function view(User $user) {
        
        return $user->user_type === "1";
    }
    
    public function create(User $user) {
        
        return $user->user_type === "1";
    }
    
    
    public function destroy(User $user) {
        
        return $user->user_type === "1";
    }
    

    public function edit(User $user) {
        if($user->user_type === "1"){
            return true;
       }else if($user->user_type === "2"){
            return true;
       }
    }    

    public function update(User $user) {
        if($user->user_type === "1"){
            return true;
       }else if($user->user_type === "2"){
            return true;
       }
    }


}