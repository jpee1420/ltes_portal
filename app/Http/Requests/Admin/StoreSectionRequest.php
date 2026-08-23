<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'school_year_id' => ['required', 'exists:school_years,id'],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections')->where(fn ($query) => $query
                    ->where('school_year_id', $this->input('school_year_id'))
                    ->where('grade_level_id', $this->input('grade_level_id'))
                ),
            ],
            'adviser_id' => ['nullable', 'exists:users,id'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'name.unique' => 'A section with this name already exists in the selected grade level and school year.',
        ];
    }
}
