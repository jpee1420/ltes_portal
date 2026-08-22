<?php

namespace App\Queries\Calendar;

use App\Models\CalendarEvent;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetUpcomingEvents
{
    /**
     * Retrieve upcoming calendar events for the user.
     *
     * @return Collection<int, array{
     *     id: int,
     *     title: string,
     *     description: string|null,
     *     category: string,
     *     category_label: string,
     *     color: string,
     *     start_date: string,
     *     end_date: string|null,
     *     is_school_wide: bool,
     *     section_name: string|null
     * }>
     */
    public function execute(User $user, int $limit = 5, ?Carbon $fromDate = null): Collection
    {
        $activeSchoolYearId = SchoolYear::query()->active()->value('id');

        $query = CalendarEvent::query()
            ->with(['section.gradeLevel'])
            ->forUser($user)
            ->upcoming($fromDate, $limit);

        if ($activeSchoolYearId) {
            $query->where('school_year_id', $activeSchoolYearId);
        }

        return $query->get()->map(function (CalendarEvent $event) {
            $sectionName = null;
            if ($event->section) {
                $sectionName = $event->section->gradeLevel
                    ? "{$event->section->gradeLevel->name} - {$event->section->name}"
                    : $event->section->name;
            }

            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'category' => $event->category->value,
                'category_label' => $event->category->label(),
                'color' => $event->category->color(),
                'start_date' => $event->start_date->toDateString(),
                'end_date' => $event->end_date?->toDateString(),
                'is_school_wide' => $event->category->isSchoolWide() && $event->section_id === null,
                'section_name' => $sectionName,
            ];
        });
    }
}
