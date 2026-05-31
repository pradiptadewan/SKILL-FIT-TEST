<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Resident;
use App\Repositories\Contracts\ResidentRepositoryInterface;
use App\Services\ResidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function __construct(
        private ResidentRepositoryInterface $residents,
        private ResidentService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->residents->paginate($request->string('search')->toString()));
    }

    public function store(StoreResidentRequest $request): JsonResponse
    {
        $resident = $this->service->create($request->safe()->except('ktp_photo'), $request->file('ktp_photo'));

        return response()->json(['message' => 'Penghuni berhasil ditambahkan.', 'data' => $resident], 201);
    }

    public function show(Resident $resident): JsonResponse
    {
        return response()->json(['data' => $resident->load(['currentOccupancy.house', 'occupancyHistories.house'])]);
    }

    public function update(UpdateResidentRequest $request, Resident $resident): JsonResponse
    {
        $resident = $this->service->update(
            $resident,
            $request->safe()->except('ktp_photo'),
            $request->file('ktp_photo')
        );

        return response()->json(['message' => 'Penghuni berhasil diperbarui.', 'data' => $resident]);
    }

    public function destroy(Resident $resident): JsonResponse
    {
        if ($resident->occupancyHistories()->exists()) {
            return response()->json(['message' => 'Penghuni yang memiliki histori hunian tidak dapat dihapus.'], 422);
        }

        $this->service->delete($resident);

        return response()->json(['message' => 'Penghuni berhasil dihapus.']);
    }
}
