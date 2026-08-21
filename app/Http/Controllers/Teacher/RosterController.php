<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RosterController extends Controller
{
    /**
     * Display the advisory class roster for the authenticated teacher.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $section = Section::with([
            'gradeLevel',
            'schoolYear',
            'studentProfiles.user',
        ])
            ->where('adviser_id', $user->id)
            ->first();

        return Inertia::render('teacher/roster/Index', [
            'section' => $section,
        ]);
    }
}
