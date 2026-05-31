<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MonthlyReportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonthlyReportController extends Controller
{
    public function __construct(private MonthlyReportRepositoryInterface $reports)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m']]);

        return response()->json([
            'data' => $this->reports->getMonthlyReport(Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()),
        ]);
    }
}
