<?php

namespace App\Policies;

use App\User;

class UserPolicy
{
    public function destroy(User $user) {
        return $user->user_type === "1";
    }

    public function update(User $user) {
        return $user->user_type === "1";
    }


}