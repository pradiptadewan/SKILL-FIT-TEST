<?php

namespace App\Models;

use App\Enums\OccupancyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class House extends Model
{
    protected $fillable = ['house_number', 'occupancy_status'];

    protected function casts(): array
    {
        return ['occupancy_status' => OccupancyStatus::class];
    }

    public function occupancyHistories(): HasMany
    {
        return $this->hasMany(HouseResidentHistory::class)->latest('started_at');
    }

    public function currentOccupancy(): HasOne
    {
        return $this->hasOne(HouseResidentHistory::class)
            ->whereNull('ended_at')
            ->latestOfMany();
    }

    public function monthlyDues(): HasMany
    {
        return $this->hasMany(MonthlyDue::class);
    }
}
