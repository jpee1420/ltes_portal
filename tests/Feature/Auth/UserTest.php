<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user model casts role to user role enum', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->isAdmin())->toBeTrue()
        ->and($admin->isTeacher())->toBeFalse()
        ->and($admin->isStudent())->toBeFalse();

    expect($teacher->role)->toBe(UserRole::Teacher)
        ->and($teacher->isAdmin())->toBeFalse()
        ->and($teacher->isTeacher())->toBeTrue()
        ->and($teacher->isStudent())->toBeFalse();

    expect($student->role)->toBe(UserRole::Student)
        ->and($student->isAdmin())->toBeFalse()
        ->and($student->isTeacher())->toBeFalse()
        ->and($student->isStudent())->toBeTrue();
});

test('admin seeder seeds the default administrator', function () {
    $this->seed(AdminSeeder::class);

    $admin = User::where('credential', '1000001')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('School Administrator')
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and(Hash::check('1000001', $admin->password))->toBeTrue();
});
