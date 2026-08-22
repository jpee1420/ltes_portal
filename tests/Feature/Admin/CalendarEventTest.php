<?php

use App\Enums\EventCategory;
use App\Models\CalendarEvent;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access the calendar index page', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'Quarter 1 Exam',
        'category' => EventCategory::Exam,
        'start_date' => '2026-09-10',
    ]);

    $response = $this->actingAs($admin)->get('/admin/calendar?year=2026&month=9');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/calendar/Index')
        ->has('events', 1)
        ->where('events.0.title', 'Quarter 1 Exam')
        ->has('categories')
    );
});

test('admin can create a school-wide calendar event', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();

    $response = $this->actingAs($admin)->post('/admin/calendar', [
        'title' => 'National Teachers Day Celebration',
        'description' => 'DepEd-wide celebration program in the gym.',
        'category' => 'deped_activity',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $created = CalendarEvent::query()->where('title', 'National Teachers Day Celebration')->first();
    expect($created)->not->toBeNull()
        ->and($created->category)->toBe(EventCategory::DepedActivity)
        ->and($created->start_date->toDateString())->toBe('2026-10-05')
        ->and($created->school_year_id)->toBe($schoolYear->id)
        ->and($created->section_id)->toBeNull()
        ->and($created->created_by)->toBe($admin->id);
});

test('admin can create a section-scoped event', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();
    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $response = $this->actingAs($admin)->post('/admin/calendar', [
        'title' => 'Grade 1 Rizal Field Trip Orientation',
        'category' => 'section_event',
        'start_date' => '2026-11-12',
        'section_id' => $section->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $created = CalendarEvent::query()->where('title', 'Grade 1 Rizal Field Trip Orientation')->first();
    expect($created)->not->toBeNull()
        ->and($created->category)->toBe(EventCategory::SectionEvent)
        ->and($created->section_id)->toBe($section->id)
        ->and($created->start_date->toDateString())->toBe('2026-11-12');
});

test('admin can update any calendar event', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();

    $event = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'Initial Title',
        'category' => EventCategory::SchoolEvent,
        'start_date' => '2026-09-01',
    ]);

    $response = $this->actingAs($admin)->put("/admin/calendar/{$event->id}", [
        'title' => 'Updated Event Title',
        'category' => 'holiday',
        'start_date' => '2026-09-02',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updated = $event->fresh();
    expect($updated->title)->toBe('Updated Event Title')
        ->and($updated->category)->toBe(EventCategory::Holiday)
        ->and($updated->start_date->toDateString())->toBe('2026-09-02');
});

test('admin can delete any calendar event', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();

    $event = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/calendar/{$event->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(CalendarEvent::find($event->id))->toBeNull();
});

test('creating calendar event validates required fields', function () {
    $admin = User::factory()->admin()->create();
    SchoolYear::factory()->active()->create();

    $response = $this->actingAs($admin)->post('/admin/calendar', [
        'title' => '',
        'category' => 'invalid_category',
        'start_date' => 'not-a-date',
    ]);

    $response->assertSessionHasErrors(['title', 'category', 'start_date']);
});
