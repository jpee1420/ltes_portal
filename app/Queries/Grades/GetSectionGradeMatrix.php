<?php

namespace App\Queries\Grades;

use App\Models\Grade;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class GetSectionGradeMatrix
{
    /**
     * Get the grade matrix for a section, specific subject, and quarter.
     *
     * @return array{
     *     section: Section,
     *     subjects: Collection<int, Subject>,
     *     selectedSubject: Subject|null,
     *     selectedQuarter: string,
     *     students: array<int, array{id: int, name: string, lrn: string, gender: string, grades: array<string, int|null>}>
     * }
     */
    public function execute(Section $section, ?int $subjectId = null, string $quarter = 'Q1'): array
    {
        $section->load(['gradeLevel.subjects', 'schoolYear']);
        $subjects = $section->gradeLevel->subjects;
        $selectedSubject = $subjectId ? $subjects->firstWhere('id', $subjectId) : $subjects->first();

        $students = $section->studentProfiles()
            ->with(['user'])
            ->orderBy('gender')
            ->orderBy('last_name')
            ->get();

        $studentIds = $students->pluck('id')->all();

        // Load all grades for these students in this school year
        $grades = Grade::whereIn('student_id', $studentIds)
            ->where('school_year_id', $section->school_year_id)
            ->when($selectedSubject, fn ($q) => $q->where('subject_id', $selectedSubject->id))
            ->get()
            ->groupBy('student_id');

        $studentRows = $students->map(function ($student) use ($grades) {
            $studentGrades = $grades->get($student->id, collect());
            $quarterGrades = [
                'Q1' => $studentGrades->firstWhere('quarter', 'Q1')?->score,
                'Q2' => $studentGrades->firstWhere('quarter', 'Q2')?->score,
                'Q3' => $studentGrades->firstWhere('quarter', 'Q3')?->score,
                'Q4' => $studentGrades->firstWhere('quarter', 'Q4')?->score,
            ];

            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'lrn' => $student->lrn,
                'gender' => $student->gender,
                'grades' => $quarterGrades,
            ];
        })->all();

        return [
            'section' => $section,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'selectedQuarter' => $quarter,
            'students' => $studentRows,
        ];
    }
}
