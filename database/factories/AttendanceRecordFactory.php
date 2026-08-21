<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
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
            'section_id' => Section::factory(),
            'date' => fake()->date(),
            'status' => fake()->randomElement(['present', 'absent', 'tardy', 'excused']),
            'remarks' => fake()->optional()->sentence(),
            'encoded_by' => User::factory()->teacher(),
        ];
    }
}
