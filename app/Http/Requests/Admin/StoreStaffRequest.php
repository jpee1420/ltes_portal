<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'employee_no' => ['required', 'string', 'size:7', 'regex:/^[0-9]{7}$/', 'unique:users,credential', 'unique:teacher_profiles,employee_no'],
            'role' => ['required', 'string', 'in:teacher,admin'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_no.size' => 'The Employee Number must be exactly 7 digits.',
            'employee_no.regex' => 'The Employee Number must contain only numbers.',
            'employee_no.unique' => 'This Employee Number is already registered.',
        ];
    }
}
