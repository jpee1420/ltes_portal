<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjectsByGrade = [
            // Kindergarten (5 subjects)
            0 => [
                'Language, Literacy, and Communication',
                'Mathematics',
                'Kagandahang Asal',
                'Kalusugan at Pagpapaunlad ng Pangangatawan',
                'Sining at Musika',
            ],
            // Grade 1 (6 subjects)
            1 => [
                'Mother Tongue',
                'Filipino',
                'English',
                'Mathematics',
                'Araling Panlipunan',
                'Edukasyon sa Pagpapakatao',
            ],
            // Grade 2 (6 subjects)
            2 => [
                'Mother Tongue',
                'Filipino',
                'English',
                'Mathematics',
                'Araling Panlipunan',
                'Edukasyon sa Pagpapakatao',
            ],
            // Grade 3 (7 subjects)
            3 => [
                'Mother Tongue',
                'Filipino',
                'English',
                'Mathematics',
                'Science',
                'Araling Panlipunan',
                'Edukasyon sa Pagpapakatao',
            ],
            // Grade 4 (8 subjects)
            4 => [
                'Filipino',
                'English',
                'Mathematics',
                'Science',
                'Araling Panlipunan',
                'Edukasyon sa Pagpapakatao',
                'Edukasyong Pantahanan at Pangkabuhayan (EPP)',
                'MAPEH',
            ],
            // Grade 5 (8 subjects)
            5 => [
                'Filipino',
                'English',
                'Mathematics',
                'Science',
                'Araling Panlipunan',
                'Edukasyon sa Pagpapakatao',
                'Edukasyong Pantahanan at Pangkabuhayan (EPP)',
                'MAPEH',
            ],
            // Grade 6 (9 subjects)
            6 => [
                'Filipino',
                'English',
                'Mathematics',
                'Science',
                'Araling Panlipunan',
                'Edukasyon sa Pagpapakatao',
                'Technology and Livelihood Education (TLE)',
                'MAPEH',
                'Good Manners and Right Conduct (GMRC)',
            ],
        ];

        foreach ($subjectsByGrade as $order => $subjects) {
            $gradeLevel = GradeLevel::where('order', $order)->first();
            if (! $gradeLevel) {
                continue;
            }

            foreach ($subjects as $index => $subjectName) {
                Subject::updateOrCreate(
                    [
                        'grade_level_id' => $gradeLevel->id,
                        'name' => $subjectName,
                    ],
                    [
                        'order' => $index + 1,
                    ]
                );
            }
        }
    }
}
