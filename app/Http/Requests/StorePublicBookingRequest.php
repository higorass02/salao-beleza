<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id'   => ['required', Rule::exists('services', 'id')->where('active', true)],
            'employee_id'  => ['required', Rule::exists('employees', 'id')->where('active', true)],
            'starts_at'    => ['required', 'date'],
            // Identificação via Google OAuth
            'google_id'    => ['nullable', 'string'],
            'google_name'  => ['nullable', 'string'],
            'google_email' => ['nullable', 'email'],
            'google_avatar'=> ['nullable', 'string'],
            // Identificação como convidado (obrigatório se não vier google_id)
            'guest_name'   => ['required_without:google_id', 'nullable', 'string', 'max:255'],
            'guest_phone'  => ['required_without:google_id', 'nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists'       => 'O serviço selecionado não está disponível.',
            'employee_id.exists'      => 'O profissional selecionado não está disponível.',
            'guest_name.required_without'  => 'Informe seu nome.',
            'guest_phone.required_without' => 'Informe seu telefone.',
        ];
    }
}
