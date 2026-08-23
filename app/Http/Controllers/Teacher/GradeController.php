<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Grades\BulkEncodeGrades;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Queries\Grades\GetSectionGradeMatrix;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    /**
     * Display the grade encoding matrix for the teacher's advisory section.
     */
    public function index(Request $request, GetSectionGradeMatrix $query): Response
    {
        $user = $request->user();

        $section = Section::where('adviser_id', $user->id)->first();

        if (! $section) {
            return Inertia::render('teacher/grades/Matrix', [
                'section' => null,
                'subjects' => [],
                'selectedSubject' => null,
                'selectedQuarter' => 'Q1',
                'students' => [],
            ]);
        }

        $subjectId = $request->integer('subject_id') ?: null;
        $quarter = $request->input('quarter', 'Q1');

        $matrix = $query->execute($section, $subjectId, $quarter);

        return Inertia::render('teacher/grades/Matrix', $matrix);
    }

    /**
     * Store bulk grades for the section.
     */
    public function store(Request $request, BulkEncodeGrades $action): RedirectResponse
    {
        $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:student_profiles,id'],
            'grades.*.subject_id' => ['required', 'exists:subjects,id'],
            'grades.*.school_year_id' => ['required', 'exists:school_years,id'],
            'grades.*.quarter' => ['required', 'in:Q1,Q2,Q3,Q4'],
            'grades.*.score' => ['required', 'integer', 'between:60,100'],
        ]);

        $grades = array_map(function ($g) use ($request) {
            $g['encoded_by'] = $request->user()->id;

            return $g;
        }, $request->input('grades'));

        $action->handle($grades);

        $firstGrade = $grades[0] ?? null;
        $quarter = $firstGrade['quarter'] ?? '';
        $section = Section::where('adviser_id', $request->user()->id)->first();

        AuditLogger::log(
            'grades.saved',
            "Saved {$quarter} grades for ".count($grades).' student records'.($section ? " in {$section->name}" : ''),
            $section
        );

        return back()->with('success', 'Grades saved successfully.');
    }
}
