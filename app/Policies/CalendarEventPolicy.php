<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    /**
     * Determine whether the user can view any calendar events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific calendar event.
     */
    public function view(User $user, CalendarEvent $event): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($event->section_id === null) {
            return true;
        }

        if ($user->role === UserRole::Teacher) {
            return $event->section?->adviser_id === $user->id;
        }

        if ($user->role === UserRole::Student) {
            return $event->section_id === $user->studentProfile?->section_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create calendar events.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Teacher], true);
    }

    /**
     * Determine whether the user can update the calendar event.
     */
    public function update(User $user, CalendarEvent $event): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Teacher) {
            return $event->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the calendar event.
     */
    public function delete(User $user, CalendarEvent $event): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Teacher) {
            return $event->created_by === $user->id;
        }

        return false;
    }
}
