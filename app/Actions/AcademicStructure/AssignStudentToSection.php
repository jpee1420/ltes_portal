<?php

namespace App\Actions\AcademicStructure;

use App\Models\StudentProfile;

class AssignStudentToSection
{
    /**
     * Assign or remove a student to/from a section.
     */
    public function handle(StudentProfile $student, ?int $sectionId = null): StudentProfile
    {
        $student->update([
            'section_id' => $sectionId,
        ]);

        return $student->refresh();
    }
}
