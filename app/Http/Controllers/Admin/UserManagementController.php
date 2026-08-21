<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UserManagement\CreateStaffAccount;
use App\Actions\UserManagement\CreateStudentAccount;
use App\Actions\UserManagement\ResetUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $users = User::with(['studentProfile', 'teacherProfile'])
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

        return Inertia::render('admin/users/Index', [
            'users' => $users,
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
     * Reset a user's password back to their initial credential number.
     */
    public function resetPassword(User $user, ResetUserPassword $action): RedirectResponse
    {
        $action->handle($user);

        return back()->with('success', "Password for {$user->name} has been reset to default ({$user->credential}).");
    }
}
