<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'action' => ['nullable', 'string', 'max:100'],
            'actor_role' => ['nullable', Rule::in(['administrator', 'management', 'operator', 'customer', 'guest'])],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $from = $validated['from'] ?? $today->subDays(29)->format('Y-m-d');
        $to = $validated['to'] ?? $today->format('Y-m-d');
        $start = CarbonImmutable::parse($from, 'Asia/Jakarta')->startOfDay()->utc();
        $end = CarbonImmutable::parse($to, 'Asia/Jakarta')->endOfDay()->utc();
        $search = trim((string) ($validated['q'] ?? ''));

        $logs = ActivityLog::query()
            ->with('actor:id,name,email,role')
            ->whereBetween('created_at', [$start, $end])
            ->when(filled($validated['action'] ?? null), fn ($query) => $query->where('action', $validated['action']))
            ->when(filled($validated['actor_role'] ?? null), function ($query) use ($validated) {
                if ($validated['actor_role'] === 'guest') {
                    $query->whereNull('actor_role');

                    return;
                }

                $query->where('actor_role', $validated['actor_role']);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('actor_name', 'like', '%'.$search.'%')
                        ->orWhere('actor_email', 'like', '%'.$search.'%')
                        ->orWhere('subject_label', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('action', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $actions = ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
        $todayStart = $today->utc();
        $todayEnd = $today->endOfDay()->utc();
        $todayLogs = ActivityLog::query()->whereBetween('created_at', [$todayStart, $todayEnd]);
        $stats = [
            'today' => (clone $todayLogs)->count(),
            'failed_logins' => (clone $todayLogs)->where('action', 'auth.login.failed')->count(),
            'unique_actors' => (clone $todayLogs)->whereNotNull('actor_user_id')->distinct('actor_user_id')->count('actor_user_id'),
        ];
        $filters = ['from' => $from, 'to' => $to, 'action' => $validated['action'] ?? '', 'actor_role' => $validated['actor_role'] ?? '', 'q' => $search];

        return view('admin.activity-logs.index', compact('logs', 'actions', 'stats', 'filters'));
    }
}
