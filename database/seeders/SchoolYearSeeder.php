<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SchoolYear::updateOrCreate(
            ['label' => '2026-2027'],
            [
                'is_active' => true,
                'start_date' => '2026-08-15',
                'end_date' => '2027-05-30',
            ]
        );
    }
}
