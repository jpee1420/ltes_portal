<?php

namespace App\Actions\Calendar;

use App\Enums\UserRole;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Validation\ValidationException;

class DeleteCalendarEvent
{
    /**
     * Delete a calendar event.
     */
    public function handle(CalendarEvent $event, User $user): bool
    {
        if ($user->role === UserRole::Teacher && $event->created_by !== $user->id) {
            throw ValidationException::withMessages([
                'event' => 'You can only delete events you created.',
            ]);
        }

        $title = $event->title;
        $deleted = (bool) $event->delete();

        if ($deleted) {
            AuditLogger::log('calendar.event_deleted', "Deleted calendar event: {$title}");
        }

        return $deleted;
    }
}
