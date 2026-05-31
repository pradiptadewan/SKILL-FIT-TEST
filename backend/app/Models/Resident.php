<?php

namespace App\Models;

use App\Enums\ResidentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Resident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'ktp_photo_path',
        'resident_status',
        'phone_number',
        'is_married',
    ];

    protected $appends = ['ktp_photo_url'];

    protected function casts(): array
    {
        return [
            'resident_status' => ResidentStatus::class,
            'is_married' => 'boolean',
        ];
    }

    public function occupancyHistories(): HasMany
    {
        return $this->hasMany(HouseResidentHistory::class);
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

    public function getKtpPhotoUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->ktp_photo_path);
    }
}
