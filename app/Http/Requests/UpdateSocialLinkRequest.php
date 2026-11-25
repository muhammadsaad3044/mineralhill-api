<?php

namespace App\Http\Requests;

use App\Models\SocialLink;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('social_link');

        return [
            'platform' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($id) {
                    if (strtolower(trim($value)) !== 'custom') {
                        $exists = SocialLink::whereRaw('LOWER(platform) = ?', [strtolower(trim($value))])
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail("A social link for '{$value}' already exists.");
                        }
                    }
                },
            ],
            'label' => ['nullable', 'string', 'max:100'],
            'url' => ['sometimes', 'required', 'string', 'regex:/^https?:\/\/.+/i'],
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
