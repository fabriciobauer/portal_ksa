<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-audit-log');

        $logs = AuditLog::query()->with('user')->latest()->paginate(30);

        return view('audit-logs.index', compact('logs'));
    }
}
