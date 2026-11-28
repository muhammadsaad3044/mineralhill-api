<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadHeroBackgroundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5 MB max
            ],
            'type' => [
                'nullable',
                'string',
                'in:desktop,mobile',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please select an image file to upload.',
            'image.mimes' => 'The background image must be a file of type: JPG, JPEG, PNG, or WEBP (SVG is not allowed).',
            'image.max' => 'The background image size must not exceed 5 MB.',
            'type.in' => 'Type must be either desktop or mobile.',
        ];
    }
}
