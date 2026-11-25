<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'label' => ['sometimes', 'required', 'string', 'max:100'],
            'value' => [
                'sometimes',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $type = strtolower($this->input('type', ''));
                    if (str_contains($type, 'email')) {
                        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail('Please enter a valid email address.');
                        }
                    } elseif (str_contains($type, 'phone')) {
                        $cleaned = preg_replace('/[^0-9]/', '', $value);
                        if (strlen($cleaned) < 7 || strlen($cleaned) > 17) {
                            $fail('Please enter a valid phone number.');
                        }
                    } elseif (in_array($type, ['website', 'store', 'marketplace', 'google maps'])) {
                        if (! preg_match('/^https?:\/\/.+/i', $value)) {
                            $fail('URL must start with http:// or https://');
                        }
                    }
                },
            ],
            'enabled' => ['nullable', 'boolean'],
        ];
    }
}
