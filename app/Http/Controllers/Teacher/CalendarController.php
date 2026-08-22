<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Calendar\CreateCalendarEvent;
use App\Actions\Calendar\DeleteCalendarEvent;
use App\Actions\Calendar\UpdateCalendarEvent;
use App\Enums\EventCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\StoreCalendarEventRequest;
use App\Http\Requests\Calendar\UpdateCalendarEventRequest;
use App\Models\CalendarEvent;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Queries\Calendar\GetCalendarEvents;
use App\Queries\Calendar\GetUpcomingEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display the teacher's monthly calendar and upcoming events.
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

        $advisedSection = $activeSchoolYear
            ? Section::query()
                ->with('gradeLevel')
                ->where('adviser_id', $request->user()->id)
                ->where('school_year_id', $activeSchoolYear->id)
                ->first()
            : null;

        $events = $getCalendarEvents->execute($request->user(), $year, $month, $activeSchoolYear?->id);
        $upcomingEvents = $getUpcomingEvents->execute($request->user(), 5);

        return Inertia::render('teacher/calendar/Index', [
            'year' => $year,
            'month' => $month,
            'schoolYear' => $activeSchoolYear ? [
                'id' => $activeSchoolYear->id,
                'label' => $activeSchoolYear->label,
            ] : null,
            'advisedSection' => $advisedSection ? [
                'id' => $advisedSection->id,
                'name' => $advisedSection->gradeLevel ? "{$advisedSection->gradeLevel->name} - {$advisedSection->name}" : $advisedSection->name,
            ] : null,
            'events' => $events,
            'upcomingEvents' => $upcomingEvents,
            'categories' => [
                [
                    'value' => EventCategory::SectionEvent->value,
                    'label' => EventCategory::SectionEvent->label(),
                    'color' => EventCategory::SectionEvent->color(),
                ],
            ],
        ]);
    }

    /**
     * Store a new section-scoped event.
     */
    public function store(
        StoreCalendarEventRequest $request,
        CreateCalendarEvent $createCalendarEvent
    ): RedirectResponse {
        $createCalendarEvent->handle($request->validated(), $request->user());

        return back()->with('success', 'Section event created successfully.');
    }

    /**
     * Update an existing section event.
     */
    public function update(
        CalendarEvent $calendarEvent,
        UpdateCalendarEventRequest $request,
        UpdateCalendarEvent $updateCalendarEvent
    ): RedirectResponse {
        $updateCalendarEvent->handle($calendarEvent, $request->validated(), $request->user());

        return back()->with('success', 'Section event updated successfully.');
    }

    /**
     * Delete an existing section event.
     */
    public function destroy(
        CalendarEvent $calendarEvent,
        Request $request,
        DeleteCalendarEvent $deleteCalendarEvent
    ): RedirectResponse {
        $deleteCalendarEvent->handle($calendarEvent, $request->user());

        return back()->with('success', 'Section event deleted successfully.');
    }
}
