<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id'   => ['required', 'exists:clients,id'],
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('active', true)],
            'service_id'  => ['required', Rule::exists('services', 'id')->where('active', true)],
            'starts_at'   => ['required', 'date'],
            'status'      => ['required', Rule::in(['scheduled', 'done', 'canceled'])],
            'notes'       => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'employee_id.exists' => 'O funcionário selecionado não está ativo.',
            'service_id.exists'  => 'O serviço selecionado não está ativo.',
            'status.in'          => 'Status inválido.',
        ];
    }
}
