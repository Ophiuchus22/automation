<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadExcelRequest extends FormRequest
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
            'file1' => [
                'required',
                'file',
                'mimes:xls,xlsx',
                'max:10240', // 10MB
            ],
            'file2' => [
                'required',
                'file',
                'mimes:xls,xlsx',
                'max:10240', // 10MB
            ],
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
            'file1.required' => 'File 1 is required.',
            'file1.mimes' => 'File 1 must be an Excel file (.xls or .xlsx).',
            'file1.max' => 'File 1 must not exceed 10MB.',
            'file2.required' => 'File 2 is required.',
            'file2.mimes' => 'File 2 must be an Excel file (.xls or .xlsx).',
            'file2.max' => 'File 2 must not exceed 10MB.',
        ];
    }
}
