<?php

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('admin can view bulk import student page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.students.import'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/users/Import')
            ->has('sections')
        );
});

test('admin can download sample CSV template', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.users.students.import.template'));

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename=student_import_template.csv');

    expect($response->streamedContent())->toContain('lrn,first_name,last_name,middle_name,gender,birth_date');
});

test('bulk import creates student accounts with derived default password', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();
    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Grade 1 - Rizal',
    ]);

    $csvContent = <<<'CSV'
lrn,first_name,last_name,middle_name,gender,birth_date
101010101001,Juan,Dela Cruz,Santos,M,2015-11-14
101010101002,Maria,Clara,Mercado,F,2015-05-08
CSV;

    $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

    $response = $this->actingAs($admin)
        ->post(route('admin.users.students.import.process'), [
            'file' => $file,
            'section_id' => $section->id,
        ]);

    $response->assertRedirect(route('admin.users.students.import'));
    $response->assertSessionHas('import_results');

    $results = session('import_results');
    expect($results['created'])->toHaveCount(2)
        ->and($results['skipped'])->toHaveCount(0)
        ->and($results['failed'])->toHaveCount(0);

    // Verify User 1 created
    $user1 = User::where('credential', '101010101001')->first();
    expect($user1)->not->toBeNull()
        ->and($user1->name)->toBe('Juan Dela Cruz')
        // 11142015 + 1001 = 111420151001
        ->and(Hash::check('111420151001', $user1->password))->toBeTrue();

    // Verify Profile 1 created and enrolled
    $profile1 = StudentProfile::where('lrn', '101010101001')->first();
    expect($profile1)->not->toBeNull()
        ->and($profile1->section_id)->toBe($section->id);
});

test('bulk import skips duplicate LRN with row-level report and records failed rows', function () {
    $admin = User::factory()->admin()->create();

    // Pre-create existing student
    User::factory()->student()->create(['credential' => '101010101001']);

    $csvContent = <<<'CSV'
lrn,first_name,last_name,middle_name,gender,birth_date
101010101001,Juan,Dela Cruz,Santos,M,2015-11-14
101010101002,Maria,Clara,,F,2015-05-08
invalid_lrn,Pedro,Penduko,,M,2015-01-01
CSV;

    $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

    $response = $this->actingAs($admin)
        ->post(route('admin.users.students.import.process'), [
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.users.students.import'));

    $results = session('import_results');
    expect($results['created'])->toHaveCount(1) // Maria Clara
        ->and($results['skipped'])->toHaveCount(1) // 101010101001 duplicate
        ->and($results['failed'])->toHaveCount(1); // invalid_lrn
});

test('non-admin cannot access bulk import', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.users.students.import'))
        ->assertForbidden();
});
