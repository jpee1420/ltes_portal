<?php

namespace App\Actions\UserManagement;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateUserAccount
{
    /**
     * Update user account and associated role profile details.
     *
     * @param  array{first_name?: string|null, last_name?: string|null, middle_name?: string|null, name?: string|null, email?: string|null, gender?: string|null, birth_date?: string|null, section_id?: int|null}  $data
     */
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $firstName = $data['first_name'] ?? null;
            $lastName = $data['last_name'] ?? null;
            $middleName = $data['middle_name'] ?? null;

            // Determine user name
            $name = $data['name'] ?? null;
            if ($firstName && $lastName) {
                $name = trim("{$firstName} {$lastName}");
            }

            $userAttributes = [];
            if ($name !== null) {
                $userAttributes['name'] = $name;
            }
            if (array_key_exists('email', $data)) {
                $userAttributes['email'] = $data['email'];
            }

            if (! empty($userAttributes)) {
                $user->update($userAttributes);
            }

            if ($user->studentProfile) {
                $profileAttributes = [];
                if ($firstName !== null) {
                    $profileAttributes['first_name'] = $firstName;
                }
                if ($lastName !== null) {
                    $profileAttributes['last_name'] = $lastName;
                }
                if (array_key_exists('middle_name', $data)) {
                    $profileAttributes['middle_name'] = $middleName;
                }
                if (! empty($data['gender'])) {
                    $gender = strtoupper(substr($data['gender'], 0, 1));
                    $profileAttributes['gender'] = $gender;
                }
                if (! empty($data['birth_date'])) {
                    $profileAttributes['birth_date'] = $data['birth_date'];
                }
                if (array_key_exists('section_id', $data)) {
                    $profileAttributes['section_id'] = $data['section_id'];
                }

                if (! empty($profileAttributes)) {
                    $user->studentProfile->update($profileAttributes);
                }
            } elseif ($user->teacherProfile) {
                $profileAttributes = [];
                if ($firstName !== null) {
                    $profileAttributes['first_name'] = $firstName;
                }
                if ($lastName !== null) {
                    $profileAttributes['last_name'] = $lastName;
                }
                if (array_key_exists('middle_name', $data)) {
                    $profileAttributes['middle_name'] = $middleName;
                }
                if (array_key_exists('birth_date', $data)) {
                    $profileAttributes['birth_date'] = $data['birth_date'];
                }

                if (! empty($profileAttributes)) {
                    $user->teacherProfile->update($profileAttributes);
                }
            }

            AuditLogger::log('user.updated', "Updated profile details for user {$user->name} ({$user->credential})", $user);

            return $user->fresh(['studentProfile.section.gradeLevel', 'teacherProfile']);
        });
    }
}
