<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($this->route('employee')->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'string', 'max:100'],
            'active' => ['boolean'],
        ];
    }
}
