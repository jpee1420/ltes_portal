<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => StudentProfile::factory(),
            'subject_id' => Subject::factory(),
            'school_year_id' => SchoolYear::factory(),
            'quarter' => fake()->randomElement(['Q1', 'Q2', 'Q3', 'Q4']),
            'score' => fake()->numberBetween(70, 98),
            'encoded_by' => User::factory()->teacher(),
        ];
    }

    /**
     * Set score to passing (>= 75).
     */
    public function passing(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(75, 98),
        ]);
    }

    /**
     * Set score to failing (< 75).
     */
    public function failing(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(60, 74),
        ]);
    }
}
