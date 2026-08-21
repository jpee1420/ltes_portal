<?php

use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('student profile belongs to a user and has full name', function () {
    $studentProfile = StudentProfile::factory()->create([
        'first_name' => 'Juan',
        'middle_name' => 'Protacio',
        'last_name' => 'Rizal',
    ]);

    expect($studentProfile->user)->toBeInstanceOf(User::class)
        ->and($studentProfile->fullName)->toBe('Juan Protacio Rizal')
        ->and($studentProfile->user->studentProfile->id)->toBe($studentProfile->id);
});

test('teacher profile belongs to a user and has full name', function () {
    $teacherProfile = TeacherProfile::factory()->create([
        'first_name' => 'Maria',
        'middle_name' => 'Clara',
        'last_name' => 'Santos',
    ]);

    expect($teacherProfile->user)->toBeInstanceOf(User::class)
        ->and($teacherProfile->fullName)->toBe('Maria Clara Santos')
        ->and($teacherProfile->user->teacherProfile->id)->toBe($teacherProfile->id);
});
