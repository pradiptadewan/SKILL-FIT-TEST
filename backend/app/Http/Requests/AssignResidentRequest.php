<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resident_id' => ['required', 'integer', 'exists:residents,id'],
            'started_at' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
