<?php

namespace App\Actions\AcademicStructure;

use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;

class ActivateSchoolYear
{
    /**
     * Activate the given school year and deactivate all others in a single transaction.
     */
    public function handle(SchoolYear $schoolYear): SchoolYear
    {
        return DB::transaction(function () use ($schoolYear) {
            SchoolYear::where('is_active', true)->update(['is_active' => false]);
            $schoolYear->update(['is_active' => true]);

            return $schoolYear->refresh();
        });
    }
}
