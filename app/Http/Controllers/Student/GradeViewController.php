<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Queries\Grades\FetchStudentReportCard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeViewController extends Controller
{
    /**
     * Display the student's quarterly grade report card.
     */
    public function index(Request $request, FetchStudentReportCard $query): Response
    {
        $user = $request->user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            return Inertia::render('student/grades/Index', [
                'reportCard' => null,
                'schoolYears' => [],
                'selectedSchoolYearId' => null,
            ]);
        }

        $schoolYears = SchoolYear::orderByDesc('is_active')->orderByDesc('start_date')->get();
        $selectedSyId = $request->integer('school_year_id') ?: optional($schoolYears->firstWhere('is_active', true))->id ?: optional($schoolYears->first())->id;
        $selectedSy = $schoolYears->firstWhere('id', $selectedSyId);

        $reportCard = $query->execute($studentProfile, $selectedSy);

        return Inertia::render('student/grades/Index', [
            'reportCard' => $reportCard,
            'schoolYears' => $schoolYears,
            'selectedSchoolYearId' => $selectedSyId,
        ]);
    }
}
