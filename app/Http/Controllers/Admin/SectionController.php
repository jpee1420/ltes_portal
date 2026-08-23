<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Academic\CreateSection;
use App\Actions\Academic\UpdateSection;
use App\Actions\AcademicStructure\AssignStudentToSection;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectionRequest;
use App\Http\Requests\Admin\UpdateSectionRequest;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    /**
     * Display a listing of the sections.
     */
    public function index(): Response
    {
        $sections = Section::with(['schoolYear', 'gradeLevel', 'adviser'])
            ->withCount('studentProfiles')
            ->latest()
            ->paginate(15)
            ->through(function ($sec) {
                $sec->enrolled_count = $sec->student_profiles_count;
                $sec->is_at_capacity = $sec->max_students !== null && $sec->student_profiles_count >= $sec->max_students;

                return $sec;
            });

        return Inertia::render('admin/sections/Index', [
            'sections' => $sections,
        ]);
    }

    /**
     * Display the specified section with enrolled and unassigned students.
     */
    public function show(Section $section): Response
    {
        $section->load([
            'schoolYear',
            'gradeLevel',
            'adviser',
            'studentProfiles.user',
        ]);

        $section->enrolled_count = $section->studentProfiles->count();
        $section->is_at_capacity = $section->isAtCapacity();

        $unassignedStudents = StudentProfile::with('user')
            ->whereNull('section_id')
            ->orderBy('last_name')
            ->get();

        return Inertia::render('admin/sections/Show', [
            'section' => $section,
            'unassignedStudents' => $unassignedStudents,
        ]);
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(): Response
    {
        return Inertia::render('admin/sections/Create', [
            'schoolYears' => SchoolYear::orderByDesc('is_active')->get(),
            'gradeLevels' => GradeLevel::orderBy('order')->get(),
            'teachers' => User::where('role', UserRole::Teacher)->orderBy('name')->get(['id', 'name', 'credential']),
        ]);
    }

    /**
     * Store a newly created section in storage.
     */
    public function store(StoreSectionRequest $request, CreateSection $action): RedirectResponse
    {
        $action->handle($request->validated());

        return redirect()->route('admin.sections.index')->with('success', 'Section successfully created.');
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Section $section): Response
    {
        $section->load(['schoolYear', 'gradeLevel', 'adviser']);

        return Inertia::render('admin/sections/Edit', [
            'section' => $section,
            'teachers' => User::where('role', UserRole::Teacher)->orderBy('name')->get(['id', 'name', 'credential']),
        ]);
    }

    /**
     * Update the specified section in storage.
     */
    public function update(UpdateSectionRequest $request, Section $section, UpdateSection $action): RedirectResponse
    {
        $action->handle($section, $request->validated());

        return redirect()->route('admin.sections.index')->with('success', 'Section successfully updated.');
    }

    /**
     * Assign a student to this section.
     */
    public function assignStudent(Request $request, Section $section, AssignStudentToSection $action): RedirectResponse
    {
        $request->validate([
            'student_profile_id' => ['required', 'exists:student_profiles,id'],
        ]);

        $student = StudentProfile::findOrFail($request->input('student_profile_id'));
        $action->handle($student, $section->id);

        return back()->with('success', "Student {$student->fullName} enrolled in {$section->name}.");
    }

    /**
     * Unassign a student from this section.
     */
    public function unassignStudent(Section $section, StudentProfile $student, AssignStudentToSection $action): RedirectResponse
    {
        $action->handle($student, null);

        return back()->with('success', "Student {$student->fullName} removed from {$section->name}.");
    }
}
