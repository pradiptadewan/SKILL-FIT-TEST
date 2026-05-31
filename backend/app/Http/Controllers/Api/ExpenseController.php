<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Expense::query()->latest('expense_date')->get()]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Pengeluaran berhasil ditambahkan.',
            'data' => Expense::create($request->validated()),
        ], 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json(['data' => $expense]);
    }

    public function update(StoreExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense->update($request->validated());

        return response()->json(['message' => 'Pengeluaran berhasil diperbarui.', 'data' => $expense]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => 'Pengeluaran berhasil dihapus.']);
    }
}
