<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phoneNumber' => ['nullable', 'string', 'regex:/^\+[0-9]{10,15}$/'],
            'whatsapp_number' => ['nullable', 'string', 'regex:/^\+[0-9]{10,15}$/'],
            'defaultMessage' => ['nullable', 'string', 'max:1000'],
            'whatsapp_message' => ['nullable', 'string', 'max:1000'],
            'enabled' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phoneNumber.regex' => 'WhatsApp number must start with + and contain 10 to 15 digits total.',
            'whatsapp_number.regex' => 'WhatsApp number must start with + and contain 10 to 15 digits total.',
        ];
    }
}
