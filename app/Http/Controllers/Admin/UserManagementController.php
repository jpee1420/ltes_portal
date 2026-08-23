<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UserManagement\BulkImportStudents;
use App\Actions\UserManagement\CreateStaffAccount;
use App\Actions\UserManagement\CreateStudentAccount;
use App\Actions\UserManagement\ResetUserPassword;
use App\Actions\UserManagement\UpdateUserAccount;
use App\Actions\UserManagement\UploadUserPhoto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportStudentsRequest;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $users = User::with(['studentProfile.section.gradeLevel', 'teacherProfile'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('credential', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sections = Section::with('gradeLevel')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => "{$s->gradeLevel?->name} - {$s->name}",
            ]);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'sections' => $sections,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function createStudent(): Response
    {
        return Inertia::render('admin/users/CreateStudent');
    }

    /**
     * Store a newly created student in storage.
     */
    public function storeStudent(StoreStudentRequest $request, CreateStudentAccount $action): RedirectResponse
    {
        $action->handle($request->validated());

        return redirect()->route('admin.users.index')->with('success', 'Student account successfully created.');
    }

    /**
     * Show the form for creating a new teacher/staff member.
     */
    public function createStaff(): Response
    {
        return Inertia::render('admin/users/CreateStaff');
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function storeStaff(StoreStaffRequest $request, CreateStaffAccount $action): RedirectResponse
    {
        $action->handle($request->validated());

        return redirect()->route('admin.users.index')->with('success', 'Staff account successfully created.');
    }

    /**
     * Show the bulk student CSV import form.
     */
    public function importStudentsForm(): Response
    {
        $activeSchoolYear = SchoolYear::query()->active()->first();

        $sections = Section::with('gradeLevel')
            ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => "{$s->gradeLevel->name} - {$s->name}",
            ]);

        return Inertia::render('admin/users/Import', [
            'sections' => $sections,
            'results' => session('import_results'),
        ]);
    }

    /**
     * Process bulk student CSV import.
     */
    public function importStudents(ImportStudentsRequest $request, BulkImportStudents $action): RedirectResponse
    {
        $file = $request->file('file');
        $sectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;

        $results = $action->handle($file, $sectionId);

        $createdCount = count($results['created']);
        $skippedCount = count($results['skipped']);
        $failedCount = count($results['failed']);

        return redirect()->route('admin.users.students.import')
            ->with('import_results', $results)
            ->with('success', "Import processed: {$createdCount} created, {$skippedCount} skipped, {$failedCount} failed.");
    }

    /**
     * Stream CSV template for student import.
     */
    public function downloadImportTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['lrn', 'first_name', 'last_name', 'middle_name', 'gender', 'birth_date']);
            fputcsv($handle, ['101010101001', 'Juan', 'Dela Cruz', 'Santos', 'M', '2015-11-14']);
            fputcsv($handle, ['101010101002', 'Maria', 'Clara', 'Mercado', 'F', '2015-05-08']);
            fclose($handle);
        }, 'student_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Reset a user's password back to their initial credential number or birthday formula.
     */
    public function resetPassword(User $user, ResetUserPassword $action): RedirectResponse
    {
        $action->handle($user);

        return back()->with('success', "Password for {$user->name} has been reset to default formula.");
    }

    /**
     * Upload an ID photo for the specified user.
     */
    public function uploadPhoto(Request $request, User $user, UploadUserPhoto $action): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $action->handle($user, $request->file('photo'));

        return back()->with('success', "Photo uploaded successfully for {$user->name}.");
    }

    /**
     * Update the specified user and associated profile details.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateUserAccount $action): RedirectResponse
    {
        $action->handle($user, $request->validated());

        return back()->with('success', "Profile details for {$user->name} updated successfully.");
    }
}
