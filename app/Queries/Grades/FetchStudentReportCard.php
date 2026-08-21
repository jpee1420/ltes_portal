<?php

namespace App\Queries\Grades;

use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\StudentProfile;

class FetchStudentReportCard
{
    /**
     * Compile student report card data with subjects, quarters, final ratings, and remarks.
     *
     * @return array{
     *     student: array{id: int, name: string, lrn: string, gender: string, section: string|null, grade_level: string|null},
     *     school_year: array{id: int, label: string},
     *     subjects: array<int, array{
     *         subject_id: int,
     *         name: string,
     *         Q1: int|null,
     *         Q2: int|null,
     *         Q3: int|null,
     *         Q4: int|null,
     *         final_rating: float|null,
     *         is_passed: bool|null,
     *         remarks: string
     *     }>,
     *     quarterly_averages: array<string, float|null>,
     *     general_average: float|null,
     *     descriptor: string
     * }
     */
    public function execute(StudentProfile $student, ?SchoolYear $schoolYear = null): array
    {
        $schoolYear = $schoolYear ?? SchoolYear::active()->first() ?? SchoolYear::latest()->first();
        $student->load(['section.gradeLevel.subjects', 'user']);

        $section = $student->section;
        $subjects = $section?->gradeLevel?->subjects ?? collect();

        $grades = Grade::where('student_id', $student->id)
            ->when($schoolYear, fn ($q) => $q->where('school_year_id', $schoolYear->id))
            ->get();

        $quarterTotals = ['Q1' => [], 'Q2' => [], 'Q3' => [], 'Q4' => []];
        $allFinalRatings = [];

        $subjectRows = $subjects->map(function ($subject) use ($grades, &$quarterTotals, &$allFinalRatings) {
            $subjectGrades = $grades->where('subject_id', $subject->id);

            $q1 = $subjectGrades->firstWhere('quarter', 'Q1')?->score;
            $q2 = $subjectGrades->firstWhere('quarter', 'Q2')?->score;
            $q3 = $subjectGrades->firstWhere('quarter', 'Q3')?->score;
            $q4 = $subjectGrades->firstWhere('quarter', 'Q4')?->score;

            if ($q1 !== null) {
                $quarterTotals['Q1'][] = $q1;
            }
            if ($q2 !== null) {
                $quarterTotals['Q2'][] = $q2;
            }
            if ($q3 !== null) {
                $quarterTotals['Q3'][] = $q3;
            }
            if ($q4 !== null) {
                $quarterTotals['Q4'][] = $q4;
            }

            $validQuarters = array_filter([$q1, $q2, $q3, $q4], fn ($s) => $s !== null);
            $finalRating = count($validQuarters) > 0
                ? round(array_sum($validQuarters) / count($validQuarters), 1)
                : null;

            if ($finalRating !== null) {
                $allFinalRatings[] = $finalRating;
            }

            $isPassed = $finalRating !== null ? ($finalRating >= 75.0) : null;
            $remarks = $isPassed === null ? 'Pending' : ($isPassed ? 'Passed' : 'Failed');

            return [
                'subject_id' => $subject->id,
                'name' => $subject->name,
                'Q1' => $q1,
                'Q2' => $q2,
                'Q3' => $q3,
                'Q4' => $q4,
                'final_rating' => $finalRating,
                'is_passed' => $isPassed,
                'remarks' => $remarks,
            ];
        })->all();

        $quarterlyAverages = [
            'Q1' => count($quarterTotals['Q1']) > 0 ? round(array_sum($quarterTotals['Q1']) / count($quarterTotals['Q1']), 1) : null,
            'Q2' => count($quarterTotals['Q2']) > 0 ? round(array_sum($quarterTotals['Q2']) / count($quarterTotals['Q2']), 1) : null,
            'Q3' => count($quarterTotals['Q3']) > 0 ? round(array_sum($quarterTotals['Q3']) / count($quarterTotals['Q3']), 1) : null,
            'Q4' => count($quarterTotals['Q4']) > 0 ? round(array_sum($quarterTotals['Q4']) / count($quarterTotals['Q4']), 1) : null,
        ];

        $generalAverage = count($allFinalRatings) > 0
            ? round(array_sum($allFinalRatings) / count($allFinalRatings), 1)
            : null;

        $descriptor = match (true) {
            $generalAverage === null => 'Incomplete',
            $generalAverage >= 90 => 'Outstanding',
            $generalAverage >= 85 => 'Very Satisfactory',
            $generalAverage >= 80 => 'Satisfactory',
            $generalAverage >= 75 => 'Fairly Satisfactory',
            default => 'Did Not Meet Expectations',
        };

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'lrn' => $student->lrn,
                'gender' => $student->gender,
                'section' => $section?->name,
                'grade_level' => $section?->gradeLevel?->name,
            ],
            'school_year' => [
                'id' => $schoolYear?->id ?? 0,
                'label' => $schoolYear?->label ?? 'N/A',
            ],
            'subjects' => $subjectRows,
            'quarterly_averages' => $quarterlyAverages,
            'general_average' => $generalAverage,
            'descriptor' => $descriptor,
        ];
    }
}
