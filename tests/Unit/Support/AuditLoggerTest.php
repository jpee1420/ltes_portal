<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('audit logger creates an audit log record with authenticated user', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    AuditLogger::log('user.created', 'Created a new user record', $user);

    expect(AuditLog::count())->toBe(1);

    $log = AuditLog::first();
    expect($log->user_id)->toBe($user->id)
        ->and($log->action)->toBe('user.created')
        ->and($log->description)->toBe('Created a new user record')
        ->and($log->subject_type)->toBe(User::class)
        ->and($log->subject_id)->toBe($user->id);
});

test('audit logger works when no user is authenticated', function () {
    AuditLogger::log('system.bootstrap', 'System initialized');

    expect(AuditLog::count())->toBe(1);

    $log = AuditLog::first();
    expect($log->user_id)->toBeNull()
        ->and($log->action)->toBe('system.bootstrap')
        ->and($log->description)->toBe('System initialized')
        ->and($log->subject_type)->toBeNull()
        ->and($log->subject_id)->toBeNull();
});
