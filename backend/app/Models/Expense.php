<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['name', 'amount', 'expense_date', 'description'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'expense_date' => 'date:Y-m-d'];
    }
}
