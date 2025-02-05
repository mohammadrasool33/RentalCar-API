<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CarPolicy
{
    /**
     * Create a new policy instance.
     */

        public function delete(User $user){
            return Auth::user()->role === 'admin';
    }

}
