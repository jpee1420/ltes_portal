<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;

class EncodeDailyAttendance
{
    /**
     * Bulk save or update daily attendance records for a section.
     *
     * @param  array<int, array{student_id: int, status: string, remarks?: string|null}>  $records
     * @return array<int, AttendanceRecord>
     */
    public function handle(int $sectionId, string $date, array $records, int $encodedBy): array
    {
        return DB::transaction(function () use ($sectionId, $date, $records, $encodedBy) {
            $saved = [];
            foreach ($records as $item) {
                $saved[] = AttendanceRecord::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'date' => $date,
                    ],
                    [
                        'section_id' => $sectionId,
                        'status' => $item['status'],
                        'remarks' => $item['remarks'] ?? null,
                        'encoded_by' => $encodedBy,
                    ]
                );
            }

            return $saved;
        });
    }
}
