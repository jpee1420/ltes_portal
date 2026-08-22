<?php

namespace App\Http\Controllers\Admin;

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
     * Display the monthly school calendar and events.
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

        $sections = $activeSchoolYear
            ? Section::query()
                ->with('gradeLevel')
                ->where('school_year_id', $activeSchoolYear->id)
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
                ->map(fn (Section $section) => [
                    'id' => $section->id,
                    'name' => $section->gradeLevel ? "{$section->gradeLevel->name} - {$section->name}" : $section->name,
                ])
            : [];

        $events = $getCalendarEvents->execute($request->user(), $year, $month, $activeSchoolYear?->id);
        $upcomingEvents = $getUpcomingEvents->execute($request->user(), 5);

        $categories = collect(EventCategory::cases())->map(fn (EventCategory $cat) => [
            'value' => $cat->value,
            'label' => $cat->label(),
            'color' => $cat->color(),
            'is_school_wide' => $cat->isSchoolWide(),
        ]);

        return Inertia::render('admin/calendar/Index', [
            'year' => $year,
            'month' => $month,
            'schoolYear' => $activeSchoolYear ? [
                'id' => $activeSchoolYear->id,
                'label' => $activeSchoolYear->label,
            ] : null,
            'events' => $events,
            'upcomingEvents' => $upcomingEvents,
            'sections' => $sections,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new calendar event.
     */
    public function store(
        StoreCalendarEventRequest $request,
        CreateCalendarEvent $createCalendarEvent
    ): RedirectResponse {
        $createCalendarEvent->handle($request->validated(), $request->user());

        return back()->with('success', 'Calendar event created successfully.');
    }

    /**
     * Update an existing calendar event.
     */
    public function update(
        CalendarEvent $calendarEvent,
        UpdateCalendarEventRequest $request,
        UpdateCalendarEvent $updateCalendarEvent
    ): RedirectResponse {
        $updateCalendarEvent->handle($calendarEvent, $request->validated(), $request->user());

        return back()->with('success', 'Calendar event updated successfully.');
    }

    /**
     * Delete a calendar event.
     */
    public function destroy(
        CalendarEvent $calendarEvent,
        Request $request,
        DeleteCalendarEvent $deleteCalendarEvent
    ): RedirectResponse {
        $deleteCalendarEvent->handle($calendarEvent, $request->user());

        return back()->with('success', 'Calendar event deleted successfully.');
    }
}
