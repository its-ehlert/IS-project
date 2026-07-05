<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\NotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $verified = $request->input('verified');
        if ($request->input('verifiedFilter') === 'verified') {
            $verified = '1';
        } elseif ($request->input('verifiedFilter') === 'unverified') {
            $verified = '0';
        }

        $reports = Report::with(['user', 'neighborhood'])
            ->neighborhood($request->integer('neighborhoodId') ?: null)
            ->verifiedFilter($verified)
            ->searchTerm($request->string('search')->toString() ?: null)
            ->orderBy('reported_at', 'desc')
            ->get();

        return $this->jsonSuccess(['reports' => ReportResource::collection($reports)]);
    }

    public function verify(int $id, NotificationDispatcher $notifications): JsonResponse
    {
        $report = Report::with('neighborhood')->findOrFail($id);
        $report->verified = true;
        $report->save();

        $notifications->notifyReportVerified($report->neighborhood_id, $report->neighborhood->name, $report->status);

        return $this->jsonSuccess(['report' => new ReportResource($report->fresh(['user', 'neighborhood']))]);
    }

    public function destroy(int $id): JsonResponse
    {
        Report::destroy($id);

        return $this->jsonSuccess();
    }
}
