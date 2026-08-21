<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Section;
use App\Models\User;

class GradePolicy
{
    /**
     * Determine whether the user can view the grade.
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return $user->studentProfile?->id === $grade->student_id;
        }

        if ($user->isTeacher()) {
            $studentSection = $grade->student?->section;

            return $studentSection && $studentSection->adviser_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can encode grades for a section.
     */
    public function encode(User $user, Section $section): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isTeacher() && $section->adviser_id === $user->id;
    }
}
