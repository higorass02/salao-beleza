<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'price'               => ['required', 'numeric', 'min:0'],
            'duration_minutes'    => ['required', 'integer', 'min:1'],
            'active'              => ['boolean'],
            'provider_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'include_house_fee'   => ['boolean'],
        ];
    }
}
