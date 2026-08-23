<?php

namespace App\Actions\UserManagement;

use App\Models\User;
use App\Support\AuditLogger;
use App\Support\DefaultPasswordGenerator;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword
{
    /**
     * Reset the user's password back to their default formula (or credential number).
     */
    public function handle(User $user): User
    {
        $defaultPassword = $user->credential;

        if ($user->isStudent() && $user->studentProfile?->birth_date) {
            $defaultPassword = DefaultPasswordGenerator::forStudent(
                $user->studentProfile->birth_date,
                $user->credential
            );
        } elseif ($user->teacherProfile?->birth_date) {
            $defaultPassword = DefaultPasswordGenerator::forStaff(
                $user->teacherProfile->birth_date,
                $user->credential
            );
        }

        $user->forceFill([
            'password' => Hash::make($defaultPassword),
        ])->save();

        AuditLogger::log('user.password_reset', "Reset default password for user {$user->name} ({$user->credential})", $user);

        return $user;
    }
}
