<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHouseRequest;
use App\Models\House;
use Illuminate\Http\JsonResponse;

class HouseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => House::query()->with('currentOccupancy.resident')->orderBy('house_number')->get()]);
    }

    public function store(StoreHouseRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Rumah berhasil ditambahkan.',
            'data' => House::create($request->validated()),
        ], 201);
    }

    public function show(House $house): JsonResponse
    {
        return response()->json(['data' => $house->load(['currentOccupancy.resident', 'occupancyHistories.resident'])]);
    }

    public function update(StoreHouseRequest $request, House $house): JsonResponse
    {
        $house->update($request->validated());

        return response()->json(['message' => 'Rumah berhasil diperbarui.', 'data' => $house]);
    }

    public function destroy(House $house): JsonResponse
    {
        if ($house->occupancyHistories()->exists()) {
            return response()->json(['message' => 'Rumah yang memiliki histori hunian tidak dapat dihapus.'], 422);
        }

        $house->delete();

        return response()->json(['message' => 'Rumah berhasil dihapus.']);
    }
}
