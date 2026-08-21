<?php

namespace App\Actions\UserManagement;

use App\Enums\UserRole;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateStaffAccount
{
    /**
     * Create a new staff user and associated teacher profile.
     *
     * @param  array{employee_no: string, role: string, first_name: string, last_name: string, middle_name?: string|null, email?: string|null}  $data
     */
    public function handle(array $data): TeacherProfile
    {
        return DB::transaction(function () use ($data) {
            $userRole = UserRole::from($data['role']);

            $user = User::create([
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'role' => $userRole,
                'credential' => $data['employee_no'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['employee_no']),
            ]);

            return TeacherProfile::create([
                'user_id' => $user->id,
                'employee_no' => $data['employee_no'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
            ]);
        });
    }
}
