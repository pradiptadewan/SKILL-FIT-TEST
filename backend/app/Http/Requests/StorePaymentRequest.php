<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resident_id' => ['required', 'integer', 'exists:residents,id'],
            'house_id' => ['required', 'integer', 'exists:houses,id'],
            'start_month' => ['required', 'date_format:Y-m'],
            'month_count' => ['required', 'integer', 'min:1', 'max:12'],
            'fee_type_ids' => ['required', 'array', 'min:1'],
            'fee_type_ids.*' => ['integer', 'distinct', 'exists:fee_types,id'],
            'paid_at' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
