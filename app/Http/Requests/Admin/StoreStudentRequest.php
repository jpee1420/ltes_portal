<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'lrn' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/', 'unique:users,credential', 'unique:student_profiles,lrn'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'in:M,F'],
            'birth_date' => ['required', 'date', 'before:today'],
            'section_id' => ['nullable', 'integer'],
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
            'lrn.size' => 'The LRN must be exactly 12 digits.',
            'lrn.regex' => 'The LRN must contain only numbers.',
            'lrn.unique' => 'This LRN is already registered.',
        ];
    }
}
