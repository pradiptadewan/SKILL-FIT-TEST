<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service)
    {
    }

    public function index(): JsonResponse
    {
        $payments = Payment::query()
            ->with(['resident', 'house', 'details.monthlyDue.feeType'])
            ->latest('paid_at')
            ->paginate(20);

        return response()->json($payments);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Pembayaran berhasil dicatat.',
            'data' => $this->service->create($request->validated()),
        ], 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json(['data' => $payment->load(['resident', 'house', 'details.monthlyDue.feeType'])]);
    }
}
