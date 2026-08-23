<?php

namespace App\Actions\Academic;

use App\Models\Section;

class CreateSection
{
    /**
     * Create a new academic section.
     *
     * @param  array{school_year_id: int, grade_level_id: int, name: string, adviser_id?: int|null, max_students?: int|null}  $data
     */
    public function handle(array $data): Section
    {
        return Section::create([
            'school_year_id' => $data['school_year_id'],
            'grade_level_id' => $data['grade_level_id'],
            'name' => $data['name'],
            'adviser_id' => $data['adviser_id'] ?? null,
            'max_students' => $data['max_students'] ?? null,
        ]);
    }
}
