<?php

use App\Enums\EventCategory;
use App\Models\CalendarEvent;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student can view calendar with school-wide and own section events', function () {
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $mySection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Section Alpha',
    ]);

    $otherSection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Section Beta',
    ]);

    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'section_id' => $mySection->id,
    ]);

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'First Quarter Exams',
        'category' => EventCategory::Exam,
        'start_date' => '2026-09-15',
        'section_id' => null,
    ]);

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'My Class Science Project Due',
        'category' => EventCategory::SectionEvent,
        'start_date' => '2026-09-22',
        'section_id' => $mySection->id,
    ]);

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'Other Section Secret Quiz',
        'category' => EventCategory::SectionEvent,
        'start_date' => '2026-09-22',
        'section_id' => $otherSection->id,
    ]);

    $response = $this->actingAs($student)->get('/student/calendar?year=2026&month=9');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/calendar/Index')
        ->has('events', 2)
        ->where('events.0.title', 'First Quarter Exams')
        ->where('events.1.title', 'My Class Science Project Due')
    );
});

test('student dashboard includes upcoming events', function () {
    $schoolYear = SchoolYear::factory()->active()->create();
    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
    ]);

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'Intramurals 2026',
        'category' => EventCategory::SchoolEvent,
        'start_date' => now()->addDays(2)->toDateString(),
    ]);

    $response = $this->actingAs($student)->get('/student/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Dashboard')
        ->has('upcomingEvents')
    );
});

test('student cannot access teacher or admin calendar post routes', function () {
    $student = User::factory()->student()->create();

    $response1 = $this->actingAs($student)->post('/admin/calendar', [
        'title' => 'Unauthorized',
    ]);
    $response1->assertForbidden();

    $response2 = $this->actingAs($student)->post('/teacher/calendar', [
        'title' => 'Unauthorized',
    ]);
    $response2->assertForbidden();
});
