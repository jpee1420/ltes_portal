<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['M', 'F']);
        $firstName = $gender === 'M' ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lrn = fake()->unique()->numerify('1###########');

        return [
            'user_id' => User::factory()->student()->state(['credential' => $lrn, 'name' => "{$firstName} ".fake()->lastName()]),
            'lrn' => $lrn,
            'first_name' => $firstName,
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->lastName(),
            'gender' => $gender,
            'birth_date' => fake()->dateTimeBetween('-12 years', '-5 years')->format('Y-m-d'),
            'section_id' => null,
        ];
    }
}
