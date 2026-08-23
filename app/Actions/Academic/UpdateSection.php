<?php

namespace App\Actions\Academic;

use App\Models\Section;

class UpdateSection
{
    /**
     * Update an existing section.
     *
     * @param  array{name: string, adviser_id?: int|null, max_students?: int|null}  $data
     */
    public function handle(Section $section, array $data): Section
    {
        $section->update([
            'name' => $data['name'],
            'adviser_id' => $data['adviser_id'] ?? null,
            'max_students' => array_key_exists('max_students', $data) ? $data['max_students'] : $section->max_students,
        ]);

        return $section;
    }
}
