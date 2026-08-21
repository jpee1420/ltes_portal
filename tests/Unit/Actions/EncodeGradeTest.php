<?php

use App\Actions\Grades\BulkEncodeGrades;
use App\Actions\Grades\EncodeGrade;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('encode grade action creates or updates quarterly grade score correctly', function () {
    $student = StudentProfile::factory()->create();
    $subject = Subject::factory()->create();
    $sy = SchoolYear::factory()->active()->create();
    $teacher = User::factory()->teacher()->create();

    $action = app(EncodeGrade::class);

    // Initial score: 88 (Passing)
    $grade = $action->handle([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'school_year_id' => $sy->id,
        'quarter' => 'Q1',
        'score' => 88,
        'encoded_by' => $teacher->id,
    ]);

    expect($grade->score)->toBe(88)
        ->and($grade->isPassing())->toBeTrue()
        ->and($grade->descriptor)->toBe('Very Satisfactory');

    // Update score: 72 (Failing)
    $updatedGrade = $action->handle([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'school_year_id' => $sy->id,
        'quarter' => 'Q1',
        'score' => 72,
        'encoded_by' => $teacher->id,
    ]);

    expect($updatedGrade->id)->toBe($grade->id)
        ->and($updatedGrade->score)->toBe(72)
        ->and($updatedGrade->isPassing())->toBeFalse()
        ->and($updatedGrade->descriptor)->toBe('Did Not Meet Expectations')
        ->and(Grade::count())->toBe(1);
});

test('encode grade rejects scores outside 60-100 range', function () {
    $student = StudentProfile::factory()->create();
    $subject = Subject::factory()->create();
    $sy = SchoolYear::factory()->active()->create();
    $teacher = User::factory()->teacher()->create();

    $action = app(EncodeGrade::class);

    expect(fn () => $action->handle([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'school_year_id' => $sy->id,
        'quarter' => 'Q1',
        'score' => 105,
        'encoded_by' => $teacher->id,
    ]))->toThrow(InvalidArgumentException::class);
});

test('bulk encode grades handles multiple grades in a transaction', function () {
    $student1 = StudentProfile::factory()->create();
    $student2 = StudentProfile::factory()->create();
    $subject = Subject::factory()->create();
    $sy = SchoolYear::factory()->active()->create();
    $teacher = User::factory()->teacher()->create();

    $bulkAction = app(BulkEncodeGrades::class);

    $results = $bulkAction->handle([
        [
            'student_id' => $student1->id,
            'subject_id' => $subject->id,
            'school_year_id' => $sy->id,
            'quarter' => 'Q2',
            'score' => 92,
            'encoded_by' => $teacher->id,
        ],
        [
            'student_id' => $student2->id,
            'subject_id' => $subject->id,
            'school_year_id' => $sy->id,
            'quarter' => 'Q2',
            'score' => 79,
            'encoded_by' => $teacher->id,
        ],
    ]);

    expect($results)->toHaveCount(2)
        ->and(Grade::where('quarter', 'Q2')->count())->toBe(2);
});
