<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\NotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private const VALID_STATUSES = ['available', 'low', 'none', 'scheduled'];

    public function index(Request $request): JsonResponse
    {
        $reports = Report::with(['user', 'neighborhood'])
            ->neighborhood($request->integer('neighborhoodId') ?: null)
            ->status($request->string('status')->toString() ?: null)
            ->verifiedFilter($request->input('verified'))
            ->searchTerm($request->string('search')->toString() ?: null)
            ->orderBy('reported_at', 'desc')
            ->when($request->filled('limit'), fn ($q) => $q->limit($request->integer('limit')))
            ->get();

        return $this->jsonSuccess(['reports' => ReportResource::collection($reports)]);
    }

    public function store(Request $request, NotificationDispatcher $notifications): JsonResponse
    {
        $neighborhoodId = (int) $request->input('neighborhoodId', 0);
        $status = (string) $request->input('status', '');
        $notes = trim((string) $request->input('notes', ''));

        if (! $neighborhoodId || ! in_array($status, self::VALID_STATUSES, true) || strlen($notes) < 10) {
            return $this->jsonError('Invalid report data. Notes must be at least 10 characters.', 422);
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'neighborhood_id' => $neighborhoodId,
            'status' => $status,
            'notes' => $notes,
        ])->load(['user', 'neighborhood']);

        $notifications->notifyReportSubmitted($report->neighborhood_id, $report->neighborhood->name, $report->status);

        return $this->jsonSuccess(['report' => new ReportResource($report)], 201);
    }
}
