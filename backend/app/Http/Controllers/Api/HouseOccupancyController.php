<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignResidentRequest;
use App\Http\Requests\EndOccupancyRequest;
use App\Models\House;
use App\Models\Resident;
use App\Services\HouseOccupancyService;
use Illuminate\Http\JsonResponse;

class HouseOccupancyController extends Controller
{
    public function __construct(private HouseOccupancyService $service)
    {
    }

    public function store(AssignResidentRequest $request, House $house): JsonResponse
    {
        $history = $this->service->assign(
            $house,
            Resident::query()->findOrFail($request->integer('resident_id')),
            $request->string('started_at')->toString()
        );

        return response()->json(['message' => 'Penghuni rumah berhasil ditetapkan.', 'data' => $history], 201);
    }

    public function destroy(EndOccupancyRequest $request, House $house): JsonResponse
    {
        $history = $this->service->end($house, $request->string('ended_at')->toString());

        return response()->json(['message' => 'Periode hunian berhasil diakhiri.', 'data' => $history]);
    }
}
