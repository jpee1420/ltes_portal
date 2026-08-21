<?php

namespace App\Actions\UserManagement;

use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\User;
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
            $user = User::create([
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'role' => UserRole::Student,
                'credential' => $data['lrn'],
                'password' => Hash::make($data['lrn']),
            ]);

            return StudentProfile::create([
                'user_id' => $user->id,
                'lrn' => $data['lrn'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'section_id' => $data['section_id'] ?? null,
            ]);
        });
    }
}
