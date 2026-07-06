<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlockedSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'starts_at'   => ['required', 'date_format:Y-m-d H:i'],
            'ends_at'     => ['required', 'date_format:Y-m-d H:i', 'after:starts_at'],
            'notes'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ends_at.after' => 'O horário de término deve ser após o início.',
        ];
    }
}
