<?php

namespace App\Actions\UserManagement;

use App\Enums\UserRole;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\DefaultPasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateStaffAccount
{
    /**
     * Create a new staff user and associated teacher profile.
     *
     * @param  array{employee_no: string, role: string, first_name: string, last_name: string, middle_name?: string|null, birth_date?: string|null, email?: string|null}  $data
     */
    public function handle(array $data): TeacherProfile
    {
        return DB::transaction(function () use ($data) {
            $userRole = UserRole::from($data['role']);

            $defaultPassword = ! empty($data['birth_date'])
                ? DefaultPasswordGenerator::forStaff($data['birth_date'], $data['employee_no'])
                : $data['employee_no'];

            $user = User::create([
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'role' => $userRole,
                'credential' => $data['employee_no'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($defaultPassword),
            ]);

            $profile = TeacherProfile::create([
                'user_id' => $user->id,
                'employee_no' => $data['employee_no'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
            ]);

            AuditLogger::log('staff.created', "Created staff account for {$user->name} (Employee No: {$data['employee_no']}, Role: {$data['role']})", $user);

            return $profile;
        });
    }
}
