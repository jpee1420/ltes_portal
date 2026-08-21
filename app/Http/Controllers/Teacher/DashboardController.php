<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the teacher overview dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $section = Section::with([
            'gradeLevel.subjects',
            'schoolYear',
        ])
            ->withCount('studentProfiles')
            ->where('adviser_id', $user->id)
            ->first();

        $today = Carbon::today()->format('Y-m-d');
        $attendanceStats = [
            'present' => 0,
            'absent' => 0,
            'tardy' => 0,
            'excused' => 0,
            'is_recorded' => false,
        ];

        if ($section) {
            $records = AttendanceRecord::where('section_id', $section->id)
                ->whereDate('date', $today)
                ->get();

            if ($records->isNotEmpty()) {
                $attendanceStats['is_recorded'] = true;
                $attendanceStats['present'] = $records->where('status', 'present')->count();
                $attendanceStats['absent'] = $records->where('status', 'absent')->count();
                $attendanceStats['tardy'] = $records->where('status', 'tardy')->count();
                $attendanceStats['excused'] = $records->where('status', 'excused')->count();
            }
        }

        return Inertia::render('teacher/Dashboard', [
            'section' => $section,
            'todayAttendance' => $attendanceStats,
            'todayDate' => Carbon::today()->toFormattedDateString(),
        ]);
    }
}
