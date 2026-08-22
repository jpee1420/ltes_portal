<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use App\Queries\Calendar\GetUpcomingEvents;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display administrator overview dashboard with key metrics.
     */
    public function index(Request $request, GetUpcomingEvents $upcomingEventsQuery): Response
    {
        $activeSchoolYear = SchoolYear::active()->first() ?? SchoolYear::latest()->first();

        $totalStudents = User::where('role', UserRole::Student)->count();
        $totalTeachers = User::where('role', UserRole::Teacher)->count();
        $totalSections = $activeSchoolYear ? Section::where('school_year_id', $activeSchoolYear->id)->count() : Section::count();

        $recentStudents = StudentProfile::with(['user', 'section.gradeLevel'])
            ->latest()
            ->take(5)
            ->get();

        $sections = Section::with(['gradeLevel', 'adviser'])
            ->withCount('studentProfiles')
            ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->orderBy('grade_level_id')
            ->get();

        $upcomingEvents = $upcomingEventsQuery->execute($request->user(), 5);

        return Inertia::render('admin/Dashboard', [
            'metrics' => [
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_sections' => $totalSections,
                'active_school_year' => $activeSchoolYear ? $activeSchoolYear->label : 'None',
            ],
            'recentStudents' => $recentStudents,
            'sections' => $sections,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
