<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $fillable = ['code', 'name', 'amount', 'is_active'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'is_active' => 'boolean'];
    }

    public function monthlyDues(): HasMany
    {
        return $this->hasMany(MonthlyDue::class);
    }
}
