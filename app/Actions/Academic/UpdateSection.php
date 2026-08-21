<?php

namespace App\Actions\Academic;

use App\Models\Section;

class UpdateSection
{
    /**
     * Update an existing section.
     *
     * @param  array{name: string, adviser_id?: int|null}  $data
     */
    public function handle(Section $section, array $data): Section
    {
        $section->update([
            'name' => $data['name'],
            'adviser_id' => $data['adviser_id'] ?? null,
        ]);

        return $section;
    }
}
