<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'action' => ['nullable', 'in:created,updated,deleted'],
            'model' => ['nullable', 'in:Project,DailyReport,ReportPhoto'],
        ]);

        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('model'), fn ($q) => $q->where('auditable_type', 'App\\Models\\' . $request->input('model')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('audit.index', compact('logs'));
    }
}