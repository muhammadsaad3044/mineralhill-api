<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('altText')) {
            $this->merge(['alt_text' => $this->input('altText')]);
        }
    }

    public function rules(): array
    {
        return [
            'alt_text' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'alt_text.max' => 'Alt text must not exceed 120 characters.',
        ];
    }
}
