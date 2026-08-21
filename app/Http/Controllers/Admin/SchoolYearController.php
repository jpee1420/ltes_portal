<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AcademicStructure\ActivateSchoolYear;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolYearRequest;
use App\Models\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of the school years.
     */
    public function index(): Response
    {
        $schoolYears = SchoolYear::withCount('sections')
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('admin/school-years/Index', [
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Show the form for creating a new school year.
     */
    public function create(): Response
    {
        return Inertia::render('admin/school-years/Create');
    }

    /**
     * Store a newly created school year in storage.
     */
    public function store(StoreSchoolYearRequest $request, ActivateSchoolYear $activator): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = (bool) ($validated['is_active'] ?? false);
        unset($validated['is_active']);

        $schoolYear = SchoolYear::create($validated);

        if ($isActive || SchoolYear::where('is_active', true)->doesntExist()) {
            $activator->handle($schoolYear);
        }

        return redirect()->route('admin.school-years.index')->with('success', 'School Year created successfully.');
    }

    /**
     * Activate the given school year.
     */
    public function activate(SchoolYear $schoolYear, ActivateSchoolYear $action): RedirectResponse
    {
        $action->handle($schoolYear);

        return back()->with('success', "School Year {$schoolYear->label} is now active.");
    }
}
