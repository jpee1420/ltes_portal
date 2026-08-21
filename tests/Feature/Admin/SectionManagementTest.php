<?php

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('admin can view the sections list', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Rizal',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.sections.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/sections/Index')
            ->has('sections.data', 1)
            ->where('sections.data.0.name', 'Rizal')
        );
});

test('admin can create a section with an assigned teacher adviser', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 2', 'order' => 2]);
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.sections.store'), [
            'school_year_id' => $sy->id,
            'grade_level_id' => $gl->id,
            'name' => 'Mabini',
            'adviser_id' => $teacher->id,
        ]);

    $response->assertRedirect(route('admin.sections.index'));

    $section = Section::where('name', 'Mabini')->first();
    expect($section)->not->toBeNull()
        ->and($section->school_year_id)->toBe($sy->id)
        ->and($section->grade_level_id)->toBe($gl->id)
        ->and($section->adviser_id)->toBe($teacher->id);
});

test('section creation rejects duplicate section name in same grade and school year', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 3', 'order' => 3]);
    Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Bonifacio',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.sections.store'), [
            'school_year_id' => $sy->id,
            'grade_level_id' => $gl->id,
            'name' => 'Bonifacio',
        ]);

    $response->assertSessionHasErrors('name');
});

test('admin can update a section and change adviser', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 4', 'order' => 4]);
    $teacher1 = User::factory()->teacher()->create();
    $teacher2 = User::factory()->teacher()->create();

    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Luna',
        'adviser_id' => $teacher1->id,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.sections.update', $section), [
            'name' => 'General Luna',
            'adviser_id' => $teacher2->id,
        ]);

    $response->assertRedirect(route('admin.sections.index'));

    $section->refresh();
    expect($section->name)->toBe('General Luna')
        ->and($section->adviser_id)->toBe($teacher2->id);
});
