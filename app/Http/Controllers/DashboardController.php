<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard): JsonResponse
    {
        $neighborhoodId = $request->filled('neighborhoodId') ? $request->integer('neighborhoodId') : null;

        return $this->jsonSuccess([
            'stats' => $dashboard->getStats(),
            'weeklyTrends' => $dashboard->getWeeklyTrends($neighborhoodId),
            'monthlyStats' => $dashboard->getMonthlyStats(),
            'neighborhoodSummary' => $dashboard->getNeighborhoodSummary(),
        ]);
    }
}
