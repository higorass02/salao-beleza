<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'performed_at'        => ['required', 'date_format:H:i'],
            'client_name'         => ['required', 'string', 'max:255'],
            'service_name'        => ['required', 'string', 'max:255'],
            'service_value'     => ['required', 'numeric'],
            'include_house_fee' => ['boolean'],
            'paid_to'             => ['nullable', 'in:provider,store'],
            'notes'               => [Rule::requiredIf(fn () => (float) ($this->service_value ?? 0) < 0), 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'performed_at.required'  => 'O horário é obrigatório.',
            'client_name.required'   => 'O nome do cliente é obrigatório.',
            'service_name.required'  => 'O nome do serviço é obrigatório.',
            'service_value.required' => 'O valor do serviço é obrigatório.',
        ];
    }
}
