<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MonthlyReportRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private MonthlyReportRepositoryInterface $reports)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(['year' => ['nullable', 'integer', 'min:2000', 'max:2100']]);

        return response()->json(['data' => $this->reports->getDashboard($validated['year'] ?? now()->year)]);
    }
}
