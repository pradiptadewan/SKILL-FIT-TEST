<?php

namespace App\Models;

use App\Enums\DueStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MonthlyDue extends Model
{
    protected $fillable = [
        'house_id',
        'resident_id',
        'house_resident_history_id',
        'fee_type_id',
        'billing_month',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_month' => 'date:Y-m',
            'paid_at' => 'datetime',
            'amount' => 'integer',
            'status' => DueStatus::class,
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function occupancyHistory(): BelongsTo
    {
        return $this->belongsTo(HouseResidentHistory::class, 'house_resident_history_id');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function paymentDetail(): HasOne
    {
        return $this->hasOne(PaymentDetail::class);
    }
}
