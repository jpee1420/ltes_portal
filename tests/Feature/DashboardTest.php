<?php

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect(route('login'));
});

test('admin can visit admin dashboard with metrics', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create(['label' => '2026-2027']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/Dashboard')
            ->has('metrics')
            ->where('metrics.active_school_year', '2026-2027')
            ->has('recentStudents')
            ->has('sections')
        );
});

test('teacher can visit teacher dashboard with section overview and attendance', function () {
    $teacher = User::factory()->teacher()->create();
    TeacherProfile::factory()->create(['user_id' => $teacher->id]);
    $sy = SchoolYear::factory()->active()->create(['label' => '2026-2027']);
    $section = Section::factory()->create([
        'adviser_id' => $teacher->id,
        'school_year_id' => $sy->id,
        'name' => 'Aguinaldo',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teacher/Dashboard')
            ->has('section')
            ->where('section.name', 'Aguinaldo')
            ->has('todayAttendance')
        );
});

test('student can visit student dashboard with academic summary', function () {
    $student = User::factory()->student()->create();
    $sy = SchoolYear::factory()->active()->create(['label' => '2026-2027']);
    $section = Section::factory()->create(['school_year_id' => $sy->id, 'name' => 'Rizal']);
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'section_id' => $section->id,
    ]);

    $response = $this->actingAs($student)->get(route('student.dashboard'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('student/Dashboard')
            ->has('student')
            ->where('student.name', $student->name)
            ->has('gradesSummary')
            ->has('attendanceSummary')
        );
});

test('teacher cannot access admin dashboard', function () {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get(route('admin.dashboard'));
    $response->assertForbidden();
});

test('student cannot access teacher dashboard', function () {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->get(route('teacher.dashboard'));
    $response->assertForbidden();
});
