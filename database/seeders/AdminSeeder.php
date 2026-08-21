<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['credential' => '1000001'],
            [
                'name' => 'School Administrator',
                'role' => UserRole::Admin,
                'email' => 'admin@ltes.edu.ph',
                'password' => Hash::make('1000001'),
            ]
        );
    }
}
