<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Attendance\EncodeDailyAttendance;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Display daily attendance recording interface for the teacher's advisory section.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $section = Section::with(['gradeLevel', 'schoolYear'])->where('adviser_id', $user->id)->first();

        if (! $section) {
            return Inertia::render('teacher/attendance/Daily', [
                'section' => null,
                'date' => Carbon::today()->format('Y-m-d'),
                'students' => [],
                'lastRecordedDate' => null,
            ]);
        }

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $students = $section->studentProfiles()
            ->with('user')
            ->orderBy('gender')
            ->orderBy('last_name')
            ->get();

        $records = AttendanceRecord::where('section_id', $section->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $studentRows = $students->map(function ($student) use ($records) {
            $record = $records->get($student->id);

            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'lrn' => $student->lrn,
                'gender' => $student->gender,
                'status' => $record ? $record->status : 'present',
                'remarks' => $record ? $record->remarks : '',
            ];
        });

        // Query the latest recorded attendance date prior to or equal to selected date
        $lastRecord = AttendanceRecord::where('section_id', $section->id)
            ->latest('date')
            ->first();

        return Inertia::render('teacher/attendance/Daily', [
            'section' => $section,
            'date' => $date,
            'students' => $studentRows,
            'lastRecordedDate' => $lastRecord ? Carbon::parse($lastRecord->date)->format('Y-m-d') : null,
        ]);
    }

    /**
     * Fetch previous attendance records for carry-forward action.
     */
    public function carryForward(Request $request): JsonResponse
    {
        $user = $request->user();
        $section = Section::where('adviser_id', $user->id)->first();

        if (! $section) {
            return response()->json([
                'last_date' => null,
                'records' => [],
            ]);
        }

        $beforeDate = $request->input('before_date');

        $query = AttendanceRecord::where('section_id', $section->id);
        if ($beforeDate) {
            $query->whereDate('date', '<=', $beforeDate);
        }

        $lastRecord = $query->latest('date')->first();

        if (! $lastRecord) {
            return response()->json([
                'last_date' => null,
                'records' => [],
            ]);
        }

        $lastDateStr = Carbon::parse($lastRecord->date)->format('Y-m-d');

        $records = AttendanceRecord::where('section_id', $section->id)
            ->whereDate('date', $lastDateStr)
            ->get()
            ->keyBy('student_id')
            ->map(fn ($r) => [
                'status' => $r->status,
                'remarks' => $r->remarks,
            ]);

        return response()->json([
            'last_date' => $lastDateStr,
            'records' => $records,
        ]);
    }

    /**
     * Save daily attendance records for the section.
     */
    public function store(Request $request, EncodeDailyAttendance $action): RedirectResponse
    {
        $user = $request->user();
        $section = Section::where('adviser_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'exists:student_profiles,id'],
            'records.*.status' => ['required', 'in:present,absent,tardy,excused'],
            'records.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $action->handle($section->id, $validated['date'], $validated['records'], $user->id);

        AuditLogger::log(
            'attendance.recorded',
            "Recorded daily attendance for {$section->name} on {$validated['date']} (".count($validated['records']).' students)',
            $section
        );

        return back()->with('success', 'Daily attendance recorded successfully.');
    }
}
