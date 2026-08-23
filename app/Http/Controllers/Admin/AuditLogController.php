<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a paginated listing of system audit logs.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $logs = AuditLog::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('credential', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('admin/audit-log/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
