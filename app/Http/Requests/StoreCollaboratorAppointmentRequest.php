<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollaboratorAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'        => ['required', 'exists:clients,id'],
            'service_id'       => ['required', Rule::exists('services', 'id')->where('active', true)],
            'starts_at'        => ['required', 'date'],
            'notes'            => ['nullable', 'string'],
            'is_recurring'      => ['boolean'],
            'recurrence_type'   => ['nullable', 'in:weekly,biweekly', 'required_if:is_recurring,true'],
            'recurrence_months' => ['nullable', 'integer', 'min:1', 'max:3', 'required_if:is_recurring,true'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists' => 'O serviço selecionado não está ativo.',
        ];
    }
}
