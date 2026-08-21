<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradeLevels = [
            ['name' => 'Kindergarten', 'order' => 0],
            ['name' => 'Grade 1', 'order' => 1],
            ['name' => 'Grade 2', 'order' => 2],
            ['name' => 'Grade 3', 'order' => 3],
            ['name' => 'Grade 4', 'order' => 4],
            ['name' => 'Grade 5', 'order' => 5],
            ['name' => 'Grade 6', 'order' => 6],
        ];

        foreach ($gradeLevels as $gl) {
            GradeLevel::updateOrCreate(
                ['order' => $gl['order']],
                ['name' => $gl['name']]
            );
        }
    }
}
