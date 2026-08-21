<?php

namespace App\Http\Requests\Admin;

use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectionRequest extends FormRequest
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
        /** @var Section $section */
        $section = $this->route('section');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections')->where(fn ($query) => $query
                    ->where('school_year_id', $section->school_year_id)
                    ->where('grade_level_id', $section->grade_level_id)
                )->ignore($section->id),
            ],
            'adviser_id' => ['nullable', 'exists:users,id'],
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
            'name.unique' => 'A section with this name already exists in this grade level and school year.',
        ];
    }
}
