<?php

use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('admin can view the create student page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.students.create'))
        ->assertOk();
});

test('admin can create a student account and profile with birthday and LRN formula as default password', function () {
    $admin = User::factory()->admin()->create();

    $studentData = [
        'lrn' => '123456789012',
        'first_name' => 'Jose',
        'last_name' => 'Rizal',
        'middle_name' => 'Mercado',
        'gender' => 'M',
        'birth_date' => '2015-06-19',
    ];

    $response = $this->actingAs($admin)
        ->post(route('admin.users.students.store'), $studentData);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('credential', '123456789012')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Student)
        ->and($user->name)->toBe('Jose Rizal')
        // Expected: 06192015 + 9012 = 061920159012
        ->and(Hash::check('061920159012', $user->password))->toBeTrue();

    $profile = StudentProfile::where('lrn', '123456789012')->first();
    expect($profile)->not->toBeNull()
        ->and($profile->user_id)->toBe($user->id)
        ->and($profile->first_name)->toBe('Jose')
        ->and($profile->gender)->toBe('M')
        ->and($profile->birth_date->format('Y-m-d'))->toBe('2015-06-19');
});

test('student creation validates lrn must be exactly 12 digits', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.users.students.store'), [
            'lrn' => '12345', // Invalid length
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'gender' => 'M',
            'birth_date' => '2015-06-19',
        ]);

    $response->assertSessionHasErrors('lrn');
    expect(User::where('name', 'Jose Rizal')->exists())->toBeFalse();
});

test('student creation rejects duplicate lrn', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->student()->create(['credential' => '123456789012']);

    $response = $this->actingAs($admin)
        ->post(route('admin.users.students.store'), [
            'lrn' => '123456789012',
            'first_name' => 'Another',
            'last_name' => 'Student',
            'gender' => 'F',
            'birth_date' => '2016-01-01',
        ]);

    $response->assertSessionHasErrors('lrn');
});

test('non-admin cannot create student accounts', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->post(route('admin.users.students.store'), [
            'lrn' => '123456789012',
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'gender' => 'M',
            'birth_date' => '2015-06-19',
        ])
        ->assertForbidden();
});

test('admin can view the create staff page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.staff.create'))
        ->assertOk();
});

test('admin can create a teacher staff account with birthday formula or employee number default password', function () {
    $admin = User::factory()->admin()->create();

    $staffData = [
        'employee_no' => '2000001',
        'role' => 'teacher',
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'middle_name' => 'Clara',
        'birth_date' => '1985-03-25',
        'email' => 'maria.santos@ltes.edu.ph',
    ];

    $response = $this->actingAs($admin)
        ->post(route('admin.users.staff.store'), $staffData);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('credential', '2000001')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Teacher)
        ->and($user->name)->toBe('Maria Santos')
        ->and($user->email)->toBe('maria.santos@ltes.edu.ph')
        // 03251985 + 0001 = 032519850001
        ->and(Hash::check('032519850001', $user->password))->toBeTrue();

    $profile = TeacherProfile::where('employee_no', '2000001')->first();
    expect($profile)->not->toBeNull()
        ->and($profile->user_id)->toBe($user->id)
        ->and($profile->first_name)->toBe('Maria')
        ->and($profile->last_name)->toBe('Santos')
        ->and($profile->birth_date?->format('Y-m-d'))->toBe('1985-03-25');
});

test('staff creation validates employee number must be exactly 7 digits', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.users.staff.store'), [
            'employee_no' => '123', // Invalid length
            'role' => 'teacher',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

    $response->assertSessionHasErrors('employee_no');
});

test('staff creation rejects duplicate employee number', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->teacher()->create(['credential' => '2000001']);

    $response = $this->actingAs($admin)
        ->post(route('admin.users.staff.store'), [
            'employee_no' => '2000001',
            'role' => 'teacher',
            'first_name' => 'Another',
            'last_name' => 'Teacher',
        ]);

    $response->assertSessionHasErrors('employee_no');
});

test('admin can view user list with search filter', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create(['name' => 'Emilio Aguinaldo', 'credential' => '111122223333']);
    $teacher = User::factory()->teacher()->create(['name' => 'Apolinario Mabini', 'credential' => '2222333']);

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'Aguinaldo']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Emilio Aguinaldo')
        );
});

test('admin can reset a user password to their formula default password', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create([
        'credential' => '123456789012',
        'password' => 'some-changed-password',
    ]);
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'birth_date' => '2015-11-14',
    ]);

    expect(Hash::check('111420159012', $student->password))->toBeFalse();

    $response = $this->actingAs($admin)
        ->post(route('admin.users.reset-password', $student));

    $response->assertRedirect();

    $student->refresh();
    // 11142015 + 9012 = 111420159012
    expect(Hash::check('111420159012', $student->password))->toBeTrue();
});
