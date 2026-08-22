<?php

namespace App\Actions\Calendar;

use App\Enums\EventCategory;
use App\Enums\UserRole;
use App\Models\CalendarEvent;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateCalendarEvent
{
    /**
     * Create a new calendar event.
     *
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     category: EventCategory|string,
     *     start_date: string,
     *     end_date?: string|null,
     *     school_year_id?: int|null,
     *     section_id?: int|null,
     * }  $data
     */
    public function handle(array $data, User $user): CalendarEvent
    {
        $category = is_string($data['category'])
            ? EventCategory::from($data['category'])
            : $data['category'];

        $schoolYearId = $data['school_year_id'] ?? null;
        if (! $schoolYearId) {
            $activeSchoolYear = SchoolYear::query()->active()->first();
            if (! $activeSchoolYear) {
                throw ValidationException::withMessages([
                    'school_year_id' => 'No active school year found.',
                ]);
            }
            $schoolYearId = $activeSchoolYear->id;
        }

        $sectionId = $data['section_id'] ?? null;

        if ($user->role === UserRole::Teacher) {
            if ($category !== EventCategory::SectionEvent) {
                throw ValidationException::withMessages([
                    'category' => 'Teachers can only post section events.',
                ]);
            }

            $advisedSection = Section::query()
                ->where('adviser_id', $user->id)
                ->where('school_year_id', $schoolYearId)
                ->first();

            if (! $advisedSection) {
                throw ValidationException::withMessages([
                    'section_id' => 'You do not have an assigned section in the active school year.',
                ]);
            }

            $sectionId = $advisedSection->id;
        } elseif ($user->role === UserRole::Admin) {
            if ($category !== EventCategory::SectionEvent) {
                $sectionId = null;
            } elseif (! $sectionId) {
                throw ValidationException::withMessages([
                    'section_id' => 'A section must be selected for section-scoped events.',
                ]);
            }
        }

        return CalendarEvent::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $category,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'school_year_id' => $schoolYearId,
            'section_id' => $sectionId,
            'created_by' => $user->id,
        ]);
    }
}
