<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
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
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5 MB max
            ],
            'alt_text' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please select an image file to upload.',
            'image.mimes' => 'Image format must be JPG, JPEG, PNG, or WebP (SVG is not supported).',
            'image.max' => 'Product image size must not exceed 5 MB.',
            'alt_text.max' => 'Alt text must not exceed 120 characters.',
        ];
    }
}
