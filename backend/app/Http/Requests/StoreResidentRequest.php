<?php

namespace App\Http\Requests;

use App\Enums\ResidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'ktp_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resident_status' => ['required', Rule::enum(ResidentStatus::class)],
            'phone_number' => ['required', 'string', 'regex:/^[0-9+ -]{8,20}$/'],
            'is_married' => ['required', 'boolean'],
        ];
    }
}
