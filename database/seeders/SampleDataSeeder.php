<?php

namespace Database\Seeders;

use App\Actions\Grades\EncodeGrade;
use App\Enums\UserRole;
use App\Models\AttendanceRecord;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Seed realistic sample data for testing role workflows.
     */
    public function run(): void
    {
        $activeSy = SchoolYear::active()->first();
        if (! $activeSy) {
            $activeSy = SchoolYear::create([
                'label' => '2026-2027',
                'start_date' => '2026-08-15',
                'end_date' => '2027-05-30',
                'is_active' => true,
            ]);
        }

        $grade1 = GradeLevel::where('order', 1)->first() ?? GradeLevel::create(['name' => 'Grade 1', 'order' => 1]);

        // 1. Create Sample Teacher
        $teacherUser = User::updateOrCreate(
            ['credential' => '2000001'],
            [
                'name' => 'Maria Santos',
                'role' => UserRole::Teacher,
                'email' => 'teacher@ltes.edu.ph',
                'password' => Hash::make('2000001'),
            ]
        );

        TeacherProfile::updateOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'employee_no' => '2000001',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
            ]
        );

        // 2. Create Sample Section
        $section = Section::updateOrCreate(
            ['school_year_id' => $activeSy->id, 'name' => 'Rizal'],
            [
                'grade_level_id' => $grade1->id,
                'adviser_id' => $teacherUser->id,
            ]
        );

        // 3. Create Sample Student
        $studentUser = User::updateOrCreate(
            ['credential' => '101010101001'],
            [
                'name' => 'Juan Dela Cruz',
                'role' => UserRole::Student,
                'email' => 'student@ltes.edu.ph',
                'password' => Hash::make('101010101001'),
            ]
        );

        $studentProfile = StudentProfile::updateOrCreate(
            ['user_id' => $studentUser->id],
            [
                'lrn' => '101010101001',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'gender' => 'M',
                'birth_date' => '2019-06-12',
                'section_id' => $section->id,
            ]
        );

        // 4. Encode sample grades
        $subjects = $grade1->subjects;
        $encodeGrade = app(EncodeGrade::class);

        foreach ($subjects as $index => $subject) {
            $baseScore = 85 + ($index % 8);
            $encodeGrade->handle([
                'student_id' => $studentProfile->id,
                'subject_id' => $subject->id,
                'school_year_id' => $activeSy->id,
                'quarter' => 'Q1',
                'score' => $baseScore,
                'encoded_by' => $teacherUser->id,
            ]);
            $encodeGrade->handle([
                'student_id' => $studentProfile->id,
                'subject_id' => $subject->id,
                'school_year_id' => $activeSy->id,
                'quarter' => 'Q2',
                'score' => min(98, $baseScore + 2),
                'encoded_by' => $teacherUser->id,
            ]);
        }

        // 5. Create sample attendance records
        $dates = [
            '2026-09-01' => ['status' => 'present', 'remarks' => null],
            '2026-09-02' => ['status' => 'present', 'remarks' => null],
            '2026-09-03' => ['status' => 'tardy', 'remarks' => 'Heavy traffic / rain'],
            '2026-09-04' => ['status' => 'present', 'remarks' => null],
            '2026-09-05' => ['status' => 'present', 'remarks' => null],
        ];

        foreach ($dates as $date => $info) {
            AttendanceRecord::updateOrCreate(
                [
                    'student_id' => $studentProfile->id,
                    'date' => $date,
                ],
                [
                    'section_id' => $section->id,
                    'status' => $info['status'],
                    'remarks' => $info['remarks'],
                    'encoded_by' => $teacherUser->id,
                ]
            );
        }
    }
}
