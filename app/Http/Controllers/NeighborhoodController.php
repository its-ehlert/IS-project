<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use Illuminate\Http\JsonResponse;

class NeighborhoodController extends Controller
{
    public function index(): JsonResponse
    {
        $neighborhoods = Neighborhood::orderBy('name')
            ->get(['id', 'name', 'area']);

        return $this->jsonSuccess(['neighborhoods' => $neighborhoods]);
    }
}
