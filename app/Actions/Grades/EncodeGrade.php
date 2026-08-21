<?php

namespace App\Actions\Grades;

use App\Models\Grade;
use InvalidArgumentException;

class EncodeGrade
{
    /**
     * Encode or update a student's quarterly grade.
     *
     * @param  array{student_id: int, subject_id: int, school_year_id: int, quarter: string, score: int, encoded_by: int}  $data
     */
    public function handle(array $data): Grade
    {
        if ($data['score'] < 60 || $data['score'] > 100) {
            throw new InvalidArgumentException('Grade score must be between 60 and 100.');
        }

        return Grade::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'subject_id' => $data['subject_id'],
                'school_year_id' => $data['school_year_id'],
                'quarter' => $data['quarter'],
            ],
            [
                'score' => $data['score'],
                'encoded_by' => $data['encoded_by'],
            ]
        );
    }
}
