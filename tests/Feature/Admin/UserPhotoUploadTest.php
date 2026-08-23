<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admin can upload an id photo for a user', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();

    $file = UploadedFile::fake()->create('id_photo.jpg', 500, 'image/jpeg');

    $response = $this->actingAs($admin)
        ->post(route('admin.users.photo', $student), [
            'photo' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $student->refresh();
    expect($student->avatar)->not->toBeNull();

    // Verify file exists on public disk
    $path = str_replace('/storage/', '', $student->avatar);
    Storage::disk('public')->assertExists($path);

    // Verify audit log
    $log = AuditLog::where('action', 'user.photo_updated')->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->subject_id)->toBe($student->id)
        ->and($log->subject_type)->toBe(User::class);
});

test('uploading a new photo replaces and deletes the previous photo file', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $user = User::factory()->teacher()->create();

    $firstFile = UploadedFile::fake()->create('first.png', 400, 'image/png');

    $this->actingAs($admin)
        ->post(route('admin.users.photo', $user), [
            'photo' => $firstFile,
        ]);

    $user->refresh();
    $firstPath = str_replace('/storage/', '', $user->avatar);
    Storage::disk('public')->assertExists($firstPath);

    // Upload second photo
    $secondFile = UploadedFile::fake()->create('second.webp', 300, 'image/webp');

    $this->actingAs($admin)
        ->post(route('admin.users.photo', $user), [
            'photo' => $secondFile,
        ]);

    $user->refresh();
    $secondPath = str_replace('/storage/', '', $user->avatar);

    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($secondPath);
});

test('photo upload validates file must be an image', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $user = User::factory()->student()->create();

    $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    $response = $this->actingAs($admin)
        ->post(route('admin.users.photo', $user), [
            'photo' => $file,
        ]);

    $response->assertSessionHasErrors('photo');
    $user->refresh();
    expect($user->avatar)->toBeNull();
});

test('photo upload validates file size cannot exceed 2mb', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $user = User::factory()->student()->create();

    // 3000KB > 2048KB
    $file = UploadedFile::fake()->create('large.jpg', 3000, 'image/jpeg');

    $response = $this->actingAs($admin)
        ->post(route('admin.users.photo', $user), [
            'photo' => $file,
        ]);

    $response->assertSessionHasErrors('photo');
    $user->refresh();
    expect($user->avatar)->toBeNull();
});

test('non-admin users cannot upload user photos', function () {
    Storage::fake('public');

    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();

    $file = UploadedFile::fake()->create('id_photo.jpg', 200, 'image/jpeg');

    $this->actingAs($teacher)
        ->post(route('admin.users.photo', $student), [
            'photo' => $file,
        ])
        ->assertForbidden();

    $this->actingAs($student)
        ->post(route('admin.users.photo', $student), [
            'photo' => $file,
        ])
        ->assertForbidden();
});
