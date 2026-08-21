<?php

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('admin can view school years list', function () {
    $admin = User::factory()->admin()->create();
    SchoolYear::factory()->active()->create(['label' => '2026-2027']);

    $response = $this->actingAs($admin)
        ->get(route('admin.school-years.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/school-years/Index')
            ->has('schoolYears', 1)
            ->where('schoolYears.0.label', '2026-2027')
        );
});

test('admin can create a new school year', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.school-years.store'), [
            'label' => '2027-2028',
            'start_date' => '2027-08-15',
            'end_date' => '2028-05-30',
            'is_active' => false,
        ]);

    $response->assertRedirect(route('admin.school-years.index'));

    expect(SchoolYear::where('label', '2027-2028')->exists())->toBeTrue();
});

test('admin can activate a school year and deactivates all others', function () {
    $admin = User::factory()->admin()->create();
    $sy1 = SchoolYear::factory()->active()->create(['label' => '2025-2026']);
    $sy2 = SchoolYear::factory()->create(['label' => '2026-2027', 'is_active' => false]);

    expect($sy1->fresh()->is_active)->toBeTrue()
        ->and($sy2->fresh()->is_active)->toBeFalse();

    $response = $this->actingAs($admin)
        ->post(route('admin.school-years.activate', $sy2));

    $response->assertRedirect();

    expect($sy1->fresh()->is_active)->toBeFalse()
        ->and($sy2->fresh()->is_active)->toBeTrue();
});
