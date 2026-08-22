<?php

namespace App\Actions\Calendar;

use App\Enums\EventCategory;
use App\Enums\UserRole;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateCalendarEvent
{
    /**
     * Update an existing calendar event.
     *
     * @param  array{
     *     title?: string,
     *     description?: string|null,
     *     category?: EventCategory|string,
     *     start_date?: string,
     *     end_date?: string|null,
     *     section_id?: int|null,
     * }  $data
     */
    public function handle(CalendarEvent $event, array $data, User $user): CalendarEvent
    {
        $category = isset($data['category'])
            ? (is_string($data['category']) ? EventCategory::from($data['category']) : $data['category'])
            : $event->category;

        $sectionId = array_key_exists('section_id', $data) ? $data['section_id'] : $event->section_id;

        if ($user->role === UserRole::Teacher) {
            if ($event->created_by !== $user->id) {
                throw ValidationException::withMessages([
                    'event' => 'You can only edit events you created.',
                ]);
            }

            if ($category !== EventCategory::SectionEvent) {
                throw ValidationException::withMessages([
                    'category' => 'Teachers can only post section events.',
                ]);
            }
        } elseif ($user->role === UserRole::Admin) {
            if ($category !== EventCategory::SectionEvent) {
                $sectionId = null;
            } elseif (! $sectionId) {
                throw ValidationException::withMessages([
                    'section_id' => 'A section must be selected for section-scoped events.',
                ]);
            }
        }

        $event->update([
            'title' => $data['title'] ?? $event->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $event->description,
            'category' => $category,
            'start_date' => $data['start_date'] ?? $event->start_date,
            'end_date' => array_key_exists('end_date', $data) ? $data['end_date'] : $event->end_date,
            'section_id' => $sectionId,
        ]);

        return $event->fresh();
    }
}
