<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class PromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*.base64' => ['nullable', 'string'],
            'images.*.mime' => ['nullable', 'string'],
            'images.*.url' => ['nullable', 'url'],
            'model_selection' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'Prompt is required.',
            'prompt.string' => 'Prompt must be a string.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
            'data' => [],
        ], 422));
    }
}
