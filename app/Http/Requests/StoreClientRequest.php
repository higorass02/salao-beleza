<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'apelido'     => ['nullable', 'string', 'max:100'],
            'email'       => ['nullable', 'email:rfc,dns'],
            'phone'       => ['nullable', 'string', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
            'notes'       => ['nullable', 'string'],
            'birth_day'   => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'   => 'Informe um e-mail válido.',
            'phone.regex'   => 'Informe o telefone no formato (00) 00000-0000.',
        ];
    }
}
