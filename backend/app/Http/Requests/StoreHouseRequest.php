<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'house_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('houses')->ignore($this->route('house')),
            ],
        ];
    }
}
