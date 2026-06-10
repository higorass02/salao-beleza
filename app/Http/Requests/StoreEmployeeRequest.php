<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email:rfc,dns', 'unique:employees,email'],
            'phone'       => ['nullable', 'string', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
            'role'        => ['required', 'string', 'max:100'],
            'active'               => ['boolean'],
            'charges_house_fee'    => ['boolean'],
            'notify_appointments'  => ['boolean'],
            'birth_day'         => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_month'       => ['nullable', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'  => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'phone.regex'  => 'Informe o telefone no formato (00) 00000-0000.',
        ];
    }
}
