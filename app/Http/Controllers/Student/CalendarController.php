<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Queries\Calendar\GetCalendarEvents;
use App\Queries\Calendar\GetUpcomingEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display the student's read-only monthly calendar and upcoming events.
     */
    public function index(
        Request $request,
        GetCalendarEvents $getCalendarEvents,
        GetUpcomingEvents $getUpcomingEvents
    ): Response {
        $now = Carbon::now();
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);

        $activeSchoolYear = SchoolYear::query()->active()->first();

        $studentSection = $request->user()->studentProfile?->section;

        $events = $getCalendarEvents->execute($request->user(), $year, $month, $activeSchoolYear?->id);
        $upcomingEvents = $getUpcomingEvents->execute($request->user(), 5);

        return Inertia::render('student/calendar/Index', [
            'year' => $year,
            'month' => $month,
            'schoolYear' => $activeSchoolYear ? [
                'id' => $activeSchoolYear->id,
                'label' => $activeSchoolYear->label,
            ] : null,
            'section' => $studentSection ? [
                'id' => $studentSection->id,
                'name' => $studentSection->gradeLevel ? "{$studentSection->gradeLevel->name} - {$studentSection->name}" : $studentSection->name,
            ] : null,
            'events' => $events,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
