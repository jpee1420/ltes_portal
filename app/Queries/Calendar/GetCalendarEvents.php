<?php

namespace App\Queries\Calendar;

use App\Enums\UserRole;
use App\Models\CalendarEvent;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Support\Collection;

class GetCalendarEvents
{
    /**
     * Retrieve and format calendar events for the specified month and user.
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
     *     section_id: int|null,
     *     section_name: string|null,
     *     created_by: int,
     *     creator_name: string|null,
     *     can_edit: bool
     * }>
     */
    public function execute(User $user, int $year, int $month, ?int $schoolYearId = null): Collection
    {
        $resolvedSchoolYearId = $schoolYearId;
        if (! $resolvedSchoolYearId) {
            $resolvedSchoolYearId = SchoolYear::query()->active()->value('id');
        }

        $query = CalendarEvent::query()
            ->with(['section.gradeLevel', 'creator'])
            ->forUser($user)
            ->inMonth($year, $month)
            ->orderBy('start_date')
            ->orderBy('id');

        if ($resolvedSchoolYearId) {
            $query->where('school_year_id', $resolvedSchoolYearId);
        }

        return $query->get()->map(function (CalendarEvent $event) use ($user) {
            $canEdit = $user->role === UserRole::Admin
                || ($user->role === UserRole::Teacher && $event->created_by === $user->id);

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
                'section_id' => $event->section_id,
                'section_name' => $sectionName,
                'created_by' => $event->created_by,
                'creator_name' => $event->creator?->name,
                'can_edit' => $canEdit,
            ];
        });
    }
}
