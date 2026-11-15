<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->module,  fn($q) => $q->where('module', $request->module))
            ->when($request->from,    fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to,      fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->orderBy('id', 'desc')
            ->paginate(30)->withQueryString();

        $users   = \App\Models\User::orderBy('name')->get();
        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();

        return view('activity-logs.index', compact('logs', 'users', 'modules'));
    }
}