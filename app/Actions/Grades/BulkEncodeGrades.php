<?php

namespace App\Actions\Grades;

use App\Models\Grade;
use Illuminate\Support\Facades\DB;

class BulkEncodeGrades
{
    public function __construct(public EncodeGrade $encoder) {}

    /**
     * Bulk encode or update multiple student grades in a transaction.
     *
     * @param  array<int, array{student_id: int, subject_id: int, school_year_id: int, quarter: string, score: int, encoded_by: int}>  $grades
     * @return array<int, Grade>
     */
    public function handle(array $grades): array
    {
        return DB::transaction(function () use ($grades) {
            $saved = [];
            foreach ($grades as $gradeData) {
                $saved[] = $this->encoder->handle($gradeData);
            }

            return $saved;
        });
    }
}
