<?php

namespace App\Queries\Attendance;

use App\Models\AttendanceRecord;
use App\Models\SchoolYear;
use App\Models\StudentProfile;

class GetStudentAttendanceSummary
{
    /**
     * Compile student attendance summary statistics and date logs.
     *
     * @return array{
     *     student: array{id: int, name: string, lrn: string, section: string|null, grade_level: string|null},
     *     school_year: array{id: int, label: string},
     *     summary: array{
     *         total_days: int,
     *         present_count: int,
     *         absent_count: int,
     *         tardy_count: int,
     *         excused_count: int,
     *         attendance_rate: float
     *     },
     *     records: array<int, array{id: int, date: string, status: string, remarks: string|null}>
     * }
     */
    public function execute(StudentProfile $student, ?SchoolYear $schoolYear = null): array
    {
        $schoolYear = $schoolYear ?? SchoolYear::active()->first() ?? SchoolYear::latest()->first();
        $student->load(['section.gradeLevel', 'user']);

        $records = AttendanceRecord::where('student_id', $student->id)
            ->when($schoolYear, function ($q) use ($schoolYear) {
                $q->whereBetween('date', [$schoolYear->start_date->format('Y-m-d'), $schoolYear->end_date->format('Y-m-d')]);
            })
            ->orderByDesc('date')
            ->get();

        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $tardyCount = $records->where('status', 'tardy')->count();
        $excusedCount = $records->where('status', 'excused')->count();
        $totalDays = $records->count();

        $attendanceRate = $totalDays > 0
            ? round((($presentCount + $tardyCount) / $totalDays) * 100, 1)
            : 100.0;

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'lrn' => $student->lrn,
                'section' => $student->section?->name,
                'grade_level' => $student->section?->gradeLevel?->name,
            ],
            'school_year' => [
                'id' => $schoolYear?->id ?? 0,
                'label' => $schoolYear?->label ?? 'N/A',
            ],
            'summary' => [
                'total_days' => $totalDays,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'tardy_count' => $tardyCount,
                'excused_count' => $excusedCount,
                'attendance_rate' => $attendanceRate,
            ],
            'records' => $records->map(fn ($r) => [
                'id' => $r->id,
                'date' => $r->date->format('Y-m-d'),
                'status' => $r->status,
                'remarks' => $r->remarks,
            ])->all(),
        ];
    }
}
