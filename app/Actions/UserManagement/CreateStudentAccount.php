<?php

namespace App\Actions\UserManagement;

use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\DefaultPasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateStudentAccount
{
    /**
     * Create a new student user and associated profile.
     *
     * @param  array{lrn: string, first_name: string, last_name: string, middle_name?: string|null, gender: string, birth_date: string, section_id?: int|null}  $data
     */
    public function handle(array $data): StudentProfile
    {
        return DB::transaction(function () use ($data) {
            $defaultPassword = DefaultPasswordGenerator::forStudent($data['birth_date'], $data['lrn']);

            $user = User::create([
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'role' => UserRole::Student,
                'credential' => $data['lrn'],
                'password' => Hash::make($defaultPassword),
            ]);

            $profile = StudentProfile::create([
                'user_id' => $user->id,
                'lrn' => $data['lrn'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'section_id' => $data['section_id'] ?? null,
            ]);

            AuditLogger::log('student.created', "Created student account for {$user->name} (LRN: {$data['lrn']})", $user);

            return $profile;
        });
    }
}
