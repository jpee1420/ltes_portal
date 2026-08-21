<?php

use App\Actions\Grades\EncodeGrade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use App\Queries\Grades\FetchStudentReportCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('fetch student report card compiles subjects, quarterly grades, averages, and remarks', function () {
    $sy = SchoolYear::factory()->active()->create(['label' => '2026-2027']);
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Mabini',
    ]);

    $math = Subject::factory()->create(['grade_level_id' => $gl->id, 'name' => 'Mathematics', 'order' => 1]);
    $eng = Subject::factory()->create(['grade_level_id' => $gl->id, 'name' => 'English', 'order' => 2]);

    $student = StudentProfile::factory()->create(['section_id' => $section->id]);
    $teacher = User::factory()->teacher()->create();

    $encodeGrade = app(EncodeGrade::class);

    // Math grades: Q1=90, Q2=85, Q3=92, Q4=93 (Avg: 90.0 -> Passed)
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $math->id, 'school_year_id' => $sy->id, 'quarter' => 'Q1', 'score' => 90, 'encoded_by' => $teacher->id]);
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $math->id, 'school_year_id' => $sy->id, 'quarter' => 'Q2', 'score' => 85, 'encoded_by' => $teacher->id]);
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $math->id, 'school_year_id' => $sy->id, 'quarter' => 'Q3', 'score' => 92, 'encoded_by' => $teacher->id]);
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $math->id, 'school_year_id' => $sy->id, 'quarter' => 'Q4', 'score' => 93, 'encoded_by' => $teacher->id]);

    // English grades: Q1=74, Q2=72, Q3=76, Q4=74 (Avg: 74.0 -> Failed)
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $eng->id, 'school_year_id' => $sy->id, 'quarter' => 'Q1', 'score' => 74, 'encoded_by' => $teacher->id]);
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $eng->id, 'school_year_id' => $sy->id, 'quarter' => 'Q2', 'score' => 72, 'encoded_by' => $teacher->id]);
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $eng->id, 'school_year_id' => $sy->id, 'quarter' => 'Q3', 'score' => 76, 'encoded_by' => $teacher->id]);
    $encodeGrade->handle(['student_id' => $student->id, 'subject_id' => $eng->id, 'school_year_id' => $sy->id, 'quarter' => 'Q4', 'score' => 74, 'encoded_by' => $teacher->id]);

    $query = app(FetchStudentReportCard::class);
    $reportCard = $query->execute($student, $sy);

    expect($reportCard['student']['name'])->toBe($student->user->name)
        ->and($reportCard['subjects'])->toHaveCount(2);

    $mathRow = collect($reportCard['subjects'])->firstWhere('subject_id', $math->id);
    expect($mathRow['final_rating'])->toBe(90.0)
        ->and($mathRow['is_passed'])->toBeTrue()
        ->and($mathRow['remarks'])->toBe('Passed');

    $engRow = collect($reportCard['subjects'])->firstWhere('subject_id', $eng->id);
    expect($engRow['final_rating'])->toBe(74.0)
        ->and($engRow['is_passed'])->toBeFalse()
        ->and($engRow['remarks'])->toBe('Failed');

    expect($reportCard['general_average'])->toBe(82.0);
});
