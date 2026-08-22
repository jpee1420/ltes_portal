<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Queries\Attendance\GetStudentAttendanceSummary;
use App\Queries\Calendar\GetUpcomingEvents;
use App\Queries\Grades\FetchStudentReportCard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the student & parent portal overview dashboard.
     */
    public function index(
        Request $request,
        FetchStudentReportCard $gradesQuery,
        GetStudentAttendanceSummary $attendanceQuery,
        GetUpcomingEvents $upcomingEventsQuery
    ): Response {
        $user = $request->user();
        $studentProfile = $user->studentProfile;

        $upcomingEvents = $upcomingEventsQuery->execute($user, 5);

        if (! $studentProfile) {
            return Inertia::render('student/Dashboard', [
                'student' => null,
                'gradesSummary' => null,
                'attendanceSummary' => null,
                'upcomingEvents' => $upcomingEvents,
            ]);
        }

        $activeSchoolYear = SchoolYear::active()->first() ?? SchoolYear::latest()->first();

        $reportCard = $gradesQuery->execute($studentProfile, $activeSchoolYear);
        $attendance = $attendanceQuery->execute($studentProfile, $activeSchoolYear);

        return Inertia::render('student/Dashboard', [
            'student' => [
                'id' => $studentProfile->id,
                'name' => $user->name,
                'lrn' => $studentProfile->lrn,
                'gender' => $studentProfile->gender,
                'birth_date' => $studentProfile->birth_date->format('F d, Y'),
                'section' => $studentProfile->section?->name,
                'grade_level' => $studentProfile->section?->gradeLevel?->name,
                'adviser' => $studentProfile->section?->adviser?->name,
                'school_year' => $activeSchoolYear?->label ?? 'N/A',
            ],
            'gradesSummary' => [
                'general_average' => $reportCard['general_average'],
                'descriptor' => $reportCard['descriptor'],
                'subject_count' => count($reportCard['subjects']),
                'quarterly_averages' => $reportCard['quarterly_averages'],
            ],
            'attendanceSummary' => $attendance['summary'],
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
