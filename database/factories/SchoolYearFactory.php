<?php

namespace Database\Factories;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolYear>
 */
class SchoolYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2024, 2028);
        $endYear = $startYear + 1;

        return [
            'label' => "{$startYear}-{$endYear}",
            'is_active' => false,
            'start_date' => "{$startYear}-08-15",
            'end_date' => "{$endYear}-05-30",
        ];
    }

    /**
     * Indicate that the school year is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
