<?php

namespace App\Actions\UserManagement;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword
{
    /**
     * Reset the user's password back to their credential number.
     */
    public function handle(User $user): User
    {
        $user->forceFill([
            'password' => Hash::make($user->credential),
        ])->save();

        return $user;
    }
}
