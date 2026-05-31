<?php

namespace App\Services;

use App\Enums\OccupancyStatus;
use App\Models\House;
use App\Models\HouseResidentHistory;
use App\Models\Resident;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HouseOccupancyService
{
    public function assign(House $house, Resident $resident, string $startedAt): HouseResidentHistory
    {
        return DB::transaction(function () use ($house, $resident, $startedAt) {
            $lockedHouse = House::query()->lockForUpdate()->findOrFail($house->id);
            $lockedResident = Resident::query()->lockForUpdate()->findOrFail($resident->id);
            $activeHouseOccupancy = HouseResidentHistory::query()
                ->where('house_id', $lockedHouse->id)
                ->whereNull('ended_at')
                ->exists();
            $activeResidentOccupancy = HouseResidentHistory::query()
                ->where('resident_id', $lockedResident->id)
                ->whereNull('ended_at')
                ->exists();

            if ($activeHouseOccupancy) {
                throw ValidationException::withMessages(['house_id' => 'Rumah masih memiliki penghuni aktif.']);
            }

            if ($activeResidentOccupancy) {
                throw ValidationException::withMessages(['resident_id' => 'Penghuni masih terdaftar aktif di rumah lain.']);
            }

            $history = HouseResidentHistory::create([
                'house_id' => $lockedHouse->id,
                'resident_id' => $lockedResident->id,
                'started_at' => $startedAt,
            ]);
            $lockedHouse->update(['occupancy_status' => OccupancyStatus::Occupied]);

            return $history->load(['house', 'resident']);
        });
    }

    public function end(House $house, string $endedAt): HouseResidentHistory
    {
        return DB::transaction(function () use ($house, $endedAt) {
            $lockedHouse = House::query()->lockForUpdate()->findOrFail($house->id);
            $history = HouseResidentHistory::query()
                ->where('house_id', $lockedHouse->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            if (! $history) {
                throw ValidationException::withMessages(['house_id' => 'Rumah tidak memiliki penghuni aktif.']);
            }

            if ($endedAt < $history->started_at->format('Y-m-d')) {
                throw ValidationException::withMessages(['ended_at' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);
            }

            $history->update(['ended_at' => $endedAt]);
            $lockedHouse->update(['occupancy_status' => OccupancyStatus::Vacant]);

            return $history->load(['house', 'resident']);
        });
    }
}
