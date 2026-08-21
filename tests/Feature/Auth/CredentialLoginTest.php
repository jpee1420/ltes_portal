<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student can authenticate with 12-digit LRN', function () {
    $student = User::factory()->student()->create([
        'credential' => '123456789012',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'credential' => '123456789012',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($student);
    $response->assertRedirect(route('student.dashboard'));
});

test('teacher can authenticate with 7-digit employee number', function () {
    $teacher = User::factory()->teacher()->create([
        'credential' => '2000001',
        'password' => 'password2000',
    ]);

    $response = $this->post(route('login.store'), [
        'credential' => '2000001',
        'password' => 'password2000',
    ]);

    $this->assertAuthenticatedAs($teacher);
    $response->assertRedirect(route('teacher.dashboard'));
});

test('admin can authenticate with 7-digit employee number', function () {
    $admin = User::factory()->admin()->create([
        'credential' => '1000001',
        'password' => 'adminpass',
    ]);

    $response = $this->post(route('login.store'), [
        'credential' => '1000001',
        'password' => 'adminpass',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('admin.dashboard'));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->student()->create([
        'credential' => '123456789012',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'credential' => '123456789012',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('credential');
});

test('ensure role middleware prevents unauthorized role access', function () {
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();

    // Teacher cannot access admin route
    $this->actingAs($teacher)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    // Student cannot access teacher route
    $this->actingAs($student)
        ->get(route('teacher.dashboard'))
        ->assertForbidden();

    // Teacher can access teacher route
    $this->actingAs($teacher)
        ->get(route('teacher.dashboard'))
        ->assertSuccessful();

    // Student can access student route
    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertSuccessful();
});
