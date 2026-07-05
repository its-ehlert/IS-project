<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function jsonSuccess(array $data = [], int $code = 200): JsonResponse
    {
        return response()->json(array_merge(['success' => true], $data), $code);
    }

    protected function jsonError(string $message, int $code = 400): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $message], $code);
    }
}
