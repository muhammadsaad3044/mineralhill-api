<?php

namespace App\Http\Requests;

use App\Models\SocialLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (strtolower(trim($value)) !== 'custom') {
                        $exists = SocialLink::whereRaw('LOWER(platform) = ?', [strtolower(trim($value))])->exists();
                        if ($exists) {
                            $fail("A social link for '{$value}' already exists.");
                        }
                    }
                },
            ],
            'label' => ['nullable', 'string', 'max:100'],
            'url' => ['required', 'string', 'regex:/^https?:\/\/.+/i'],
            'enabled' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.regex' => 'Social media URL must start with http:// or https://',
        ];
    }
}
