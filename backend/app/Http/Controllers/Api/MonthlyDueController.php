<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonthlyDue;
use App\Services\MonthlyDueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonthlyDueController extends Controller
{
    public function __construct(private MonthlyDueService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:paid,unpaid'],
        ]);

        $dues = MonthlyDue::query()
            ->with(['house', 'resident', 'feeType'])
            ->when($validated['month'] ?? null, fn ($query, $month) => $query->where('billing_month', "{$month}-01"))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('billing_month')
            ->paginate(25);

        return response()->json($dues);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $dues = $this->service->generate($validated['month']);

        return response()->json([
            'message' => "Tagihan {$validated['month']} berhasil disiapkan.",
            'generated_count' => $dues->count(),
        ]);
    }
}
