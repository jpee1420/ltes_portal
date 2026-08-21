<?php

namespace Database\Factories;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $employeeNo = fake()->unique()->numerify('2######');
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'user_id' => User::factory()->teacher()->state(['credential' => $employeeNo, 'name' => "{$firstName} {$lastName}"]),
            'employee_no' => $employeeNo,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => fake()->lastName(),
        ];
    }
}
