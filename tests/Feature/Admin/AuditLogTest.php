<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('admin can view audit logs with pagination and search filter', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Boss']);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'student.created',
        'description' => 'Created student Juan Dela Cruz',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'calendar.event_created',
        'description' => 'Created First Periodical Exam',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.audit-log.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/audit-log/Index')
            ->has('logs.data', 2)
        );

    $filteredResponse = $this->actingAs($admin)->get(route('admin.audit-log.index', ['search' => 'Juan']));

    $filteredResponse->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/audit-log/Index')
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'student.created')
        );
});

test('non-admin cannot access audit logs', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get('/admin/audit-log')
        ->assertForbidden();

    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get('/admin/audit-log')
        ->assertForbidden();
});

test('login and logout fire audit log records', function () {
    $user = User::factory()->admin()->create(['credential' => '1000001', 'password' => 'secret123456']);

    $this->post('/login', [
        'credential' => '1000001',
        'password' => 'secret123456',
    ]);

    expect(AuditLog::where('action', 'auth.login')->where('user_id', $user->id)->exists())->toBeTrue();

    $this->post('/logout');

    expect(AuditLog::where('action', 'auth.logout')->where('user_id', $user->id)->exists())->toBeTrue();
});
