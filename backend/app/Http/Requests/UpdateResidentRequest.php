<?php

namespace App\Http\Requests;

class UpdateResidentRequest extends StoreResidentRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['ktp_photo'] = ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

        return $rules;
    }
}
