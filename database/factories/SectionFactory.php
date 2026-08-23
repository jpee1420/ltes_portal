<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_year_id' => SchoolYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'name' => fake()->randomElement(['Mabini', 'Rizal', 'Bonifacio', 'Luna', 'Silang', 'Aguinaldo', 'Del Pilar', 'Jacinto']) . ' ' . fake()->unique()->numberBetween(1, 99999),
            'adviser_id' => null,
        ];
    }

    /**
     * Set a teacher adviser for the section.
     */
    public function withAdviser(?User $adviser = null): static
    {
        return $this->state(fn (array $attributes) => [
            'adviser_id' => $adviser?->id ?? User::factory()->teacher(),
        ]);
    }
}
