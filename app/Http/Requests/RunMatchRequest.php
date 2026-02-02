<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunMatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file1_path' => 'required|string',
            'file2_path' => 'required|string',
            'file1_sheet' => 'required|string',
            'file2_sheet' => 'required|string',
            'match_mode' => 'required|in:business,person',
            'file1_column' => 'required|string|regex:/^[A-Z]{1,2}$/',
            'file2_column' => 'required_if:match_mode,business|nullable|string|regex:/^[A-Z]{1,2}$/',
            'file2_first_name_column' => 'required_if:match_mode,person|nullable|string|regex:/^[A-Z]{1,2}$/',
            'file2_last_name_column' => 'required_if:match_mode,person|nullable|string|regex:/^[A-Z]{1,2}$/',
            'threshold' => 'required|integer|min:0|max:100',
            'show_possible' => 'nullable|boolean',
            'file1_start_row' => 'nullable|integer|min:1',
            'file2_start_row' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'file1_column.regex' => 'File 1 column must be a valid column letter (A-Z or AA-ZZ).',
            'file2_column.regex' => 'File 2 column must be a valid column letter (A-Z or AA-ZZ).',
            'file2_first_name_column.regex' => 'First name column must be a valid column letter (A-Z or AA-ZZ).',
            'file2_last_name_column.regex' => 'Last name column must be a valid column letter (A-Z or AA-ZZ).',
            'match_mode.in' => 'Match mode must be either "business" or "person".',
        ];
    }
}
