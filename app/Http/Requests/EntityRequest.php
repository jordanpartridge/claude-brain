<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization needs
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable'], // Allow any type of metadata value
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'entity type',
            'name' => 'entity name',
            'metadata' => 'metadata',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'An entity type is required',
            'type.max' => 'The entity type cannot be longer than 50 characters',
            'name.required' => 'An entity name is required',
            'name.max' => 'The entity name cannot be longer than 255 characters',
            'metadata.array' => 'The metadata must be a valid JSON object',
        ];
    }
}
