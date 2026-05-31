<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use Illuminate\Http\JsonResponse;

class FeeTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => FeeType::query()->where('is_active', true)->orderBy('name')->get()]);
    }
}
