<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeLevel>
 */
class GradeLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = fake()->unique()->numberBetween(10, 99);

        return [
            'name' => "Grade {$order}",
            'order' => $order,
        ];
    }
}
