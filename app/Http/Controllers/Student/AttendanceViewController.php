<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Queries\Attendance\GetStudentAttendanceSummary;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceViewController extends Controller
{
    /**
     * Display the student's attendance summary and log history.
     */
    public function index(Request $request, GetStudentAttendanceSummary $query): Response
    {
        $user = $request->user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            return Inertia::render('student/attendance/Index', [
                'attendanceData' => null,
                'schoolYears' => [],
                'selectedSchoolYearId' => null,
            ]);
        }

        $schoolYears = SchoolYear::orderByDesc('is_active')->orderByDesc('start_date')->get();
        $selectedSyId = $request->integer('school_year_id') ?: optional($schoolYears->firstWhere('is_active', true))->id ?: optional($schoolYears->first())->id;
        $selectedSy = $schoolYears->firstWhere('id', $selectedSyId);

        $attendanceData = $query->execute($studentProfile, $selectedSy);

        return Inertia::render('student/attendance/Index', [
            'student' => $attendanceData['student'],
            'school_year' => $attendanceData['school_year'],
            'summary' => $attendanceData['summary'],
            'records' => $attendanceData['records'],
            'schoolYears' => $schoolYears,
            'selectedSchoolYearId' => $selectedSyId,
        ]);
    }
}
