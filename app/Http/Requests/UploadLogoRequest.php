<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => [
                'required',
                'file',
                'mimes:png,jpg,jpeg,svg,webp',
                'max:2048', // 2 MB max
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'Please select a logo file to upload.',
            'logo.mimes' => 'Logo must be an image of type: PNG, JPG, JPEG, SVG, or WEBP.',
            'logo.max' => 'Logo size must not exceed 2 MB.',
        ];
    }
}
