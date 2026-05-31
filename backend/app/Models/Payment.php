<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = ['resident_id', 'house_id', 'paid_at', 'total_amount', 'notes'];

    protected function casts(): array
    {
        return ['paid_at' => 'date', 'total_amount' => 'integer'];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PaymentDetail::class);
    }
}
