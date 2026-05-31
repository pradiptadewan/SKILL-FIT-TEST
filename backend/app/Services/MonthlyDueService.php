<?php

namespace App\Services;

use App\Models\FeeType;
use App\Models\HouseResidentHistory;
use App\Models\MonthlyDue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyDueService
{
    public function generate(string $month): Collection
    {
        $billingMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $histories = $this->occupanciesForMonth($billingMonth)->get();
        $feeTypes = FeeType::query()->where('is_active', true)->get();

        return $histories->flatMap(function (HouseResidentHistory $history) use ($billingMonth, $feeTypes) {
            return $feeTypes->map(fn (FeeType $feeType) => $this->firstOrCreate($history, $feeType, $billingMonth));
        });
    }

    public function firstOrCreate(
        HouseResidentHistory $history,
        FeeType $feeType,
        Carbon $billingMonth
    ): MonthlyDue {
        return MonthlyDue::query()->firstOrCreate(
            [
                'house_id' => $history->house_id,
                'resident_id' => $history->resident_id,
                'fee_type_id' => $feeType->id,
                'billing_month' => $billingMonth->toDateString(),
            ],
            [
                'house_resident_history_id' => $history->id,
                'amount' => $feeType->amount,
            ]
        );
    }

    public function findOccupancy(int $houseId, int $residentId, Carbon $billingMonth): ?HouseResidentHistory
    {
        return $this->occupanciesForMonth($billingMonth)
            ->where('house_id', $houseId)
            ->where('resident_id', $residentId)
            ->first();
    }

    private function occupanciesForMonth(Carbon $billingMonth)
    {
        return HouseResidentHistory::query()
            ->whereDate('started_at', '<=', $billingMonth->copy()->endOfMonth())
            ->where(function ($query) use ($billingMonth) {
                $query->whereNull('ended_at')
                    ->orWhereDate('ended_at', '>=', $billingMonth->copy()->startOfMonth());
            });
    }
}
