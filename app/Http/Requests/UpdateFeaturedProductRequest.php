<?php

namespace App\Http\Requests;

use App\Rules\ValidWhatsAppTemplate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFeaturedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('eyebrowText')) $merge['eyebrow_text'] = $this->input('eyebrowText');
        if ($this->has('titleLine1')) $merge['title_line_1'] = $this->input('titleLine1');
        if ($this->has('titleLine2')) $merge['title_line_2'] = $this->input('titleLine2');
        if ($this->has('currencySymbol')) $merge['currency_symbol'] = $this->input('currencySymbol');
        if ($this->has('ratingMode')) $merge['rating_mode'] = $this->input('ratingMode');
        if ($this->has('ratingValue')) $merge['rating_value'] = $this->input('ratingValue');
        if ($this->has('ratingCount')) $merge['rating_count'] = $this->input('ratingCount');
        if ($this->has('ctaText')) $merge['cta_text'] = $this->input('ctaText');
        if ($this->has('whatsappMessageTemplate')) $merge['whatsapp_message_template'] = $this->input('whatsappMessageTemplate');
        if ($this->has('showQuantitySelector')) $merge['show_quantity_selector'] = $this->input('showQuantitySelector');
        if ($this->has('isActive')) $merge['is_active'] = $this->input('isActive');

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'eyebrow_text' => ['nullable', 'string', 'max:60'],
            'title_line_1' => ['required', 'string', 'max:60'],
            'title_line_2' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:400'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'rating_mode' => ['required', 'string', 'in:auto,manual'],
            'rating_value' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'rating_count' => ['nullable', 'integer', 'min:0'],
            'cta_text' => ['nullable', 'string', 'max:30'],
            'whatsapp_message_template' => ['required', 'string', 'max:600', new ValidWhatsAppTemplate()],
            'show_quantity_selector' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title_line_1.required' => 'Title line 1 is required.',
            'title_line_1.max' => 'Title line 1 must not exceed 60 characters.',
            'title_line_2.required' => 'Title line 2 is required.',
            'title_line_2.max' => 'Title line 2 must not exceed 60 characters.',
            'description.max' => 'Description must not exceed 400 characters.',
            'rating_mode.in' => 'Rating mode must be either auto or manual.',
            'rating_value.max' => 'Rating value cannot exceed 5.0.',
            'whatsapp_message_template.required' => 'WhatsApp message template is required.',
            'whatsapp_message_template.max' => 'WhatsApp message template must not exceed 600 characters.',
        ];
    }
}
