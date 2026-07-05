<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Http\Resources\UserResource;
use App\Models\Report;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(DashboardService $dashboard): JsonResponse
    {
        $pendingReports = Report::with(['user', 'neighborhood'])
            ->where('verified', false)
            ->orderBy('reported_at', 'desc')
            ->limit(5)
            ->get();

        $recentUsers = User::with('neighborhood')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalReports = Report::count();
        $reportsToday = Report::whereDate('reported_at', now()->toDateString())->count();

        return $this->jsonSuccess([
            'stats' => [
                'totalReports' => $totalReports,
                'pendingReports' => Report::where('verified', false)->count(),
                'totalUsers' => User::count(),
                'activeUsers' => User::where('status', 'active')->count(),
                'reportsToday' => $reportsToday,
            ],
            'pendingReports' => ReportResource::collection($pendingReports),
            'recentUsers' => UserResource::collection($recentUsers),
            'activity' => $dashboard->getActivityByDay(),
            'events' => [
                ['time' => now()->toIso8601String(), 'level' => 'info', 'component' => 'System', 'message' => 'Admin dashboard loaded'],
                ['time' => now()->subHour()->toIso8601String(), 'level' => 'info', 'component' => 'Reports', 'message' => "{$totalReports} total reports in database"],
                ['time' => now()->subHours(2)->toIso8601String(), 'level' => 'success', 'component' => 'Database', 'message' => 'MySQL connection operational'],
            ],
        ]);
    }
}
