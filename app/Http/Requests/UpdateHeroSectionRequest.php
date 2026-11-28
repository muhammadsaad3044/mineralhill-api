<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->exists('titleLine1') && ! $this->exists('title_line_1')) {
            $merge['title_line_1'] = $this->input('titleLine1');
        }
        if ($this->exists('titleLine2') && ! $this->exists('title_line_2')) {
            $merge['title_line_2'] = $this->input('titleLine2');
        }
        if ($this->exists('badgeText') && ! $this->exists('badge_text')) {
            $merge['badge_text'] = $this->input('badgeText');
        }
        if ($this->exists('ctaText') && ! $this->exists('cta_text')) {
            $merge['cta_text'] = $this->input('ctaText');
        }
        if ($this->exists('ctaUrl') && ! $this->exists('cta_url')) {
            $merge['cta_url'] = $this->input('ctaUrl');
        }
        if ($this->exists('ctaEnabled') && ! $this->exists('cta_enabled')) {
            $merge['cta_enabled'] = $this->input('ctaEnabled');
        }
        if ($this->exists('overlayOpacity') && ! $this->exists('overlay_opacity')) {
            $merge['overlay_opacity'] = $this->input('overlayOpacity');
        }
        if ($this->exists('textAlign') && ! $this->exists('text_align')) {
            $merge['text_align'] = $this->input('textAlign');
        }
        if ($this->exists('isActive') && ! $this->exists('is_active')) {
            $merge['is_active'] = $this->input('isActive');
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'title_line_1' => ['required', 'string', 'max:60'],
            'title_line_2' => ['required', 'string', 'max:60'],
            'badge_text' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:250'],
            'cta_text' => ['nullable', 'string', 'max:30'],
            'cta_url' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value === null) {
                        return;
                    }

                    $trimmed = trim($value);
                    if ($trimmed === '') {
                        $fail('The CTA URL may not be whitespace only.');
                        return;
                    }

                    // 1. In-page anchor: starting with "#", letters, digits, hyphen, underscore only
                    if (str_starts_with($trimmed, '#')) {
                        if (! preg_match('/^#[A-Za-z0-9_-]+$/', $trimmed)) {
                            $fail('The CTA anchor must start with # and contain only letters, digits, hyphens, and underscores (e.g. #shop).');
                        }
                        return;
                    }

                    // 2. Internal path: starting with "/"
                    if (str_starts_with($trimmed, '/')) {
                        if (str_starts_with($trimmed, '//') || preg_match('/\s/', $trimmed)) {
                            $fail('The internal path must start with a single / and contain no spaces (e.g. /shop).');
                        }
                        return;
                    }

                    // 3. Full http:// or https:// URL
                    if (preg_match('/^https?:\/\//i', $trimmed) && filter_var($trimmed, FILTER_VALIDATE_URL) !== false) {
                        return;
                    }

                    $fail('The CTA URL must be an internal path starting with "/", an in-page anchor starting with "#" (e.g. #shop), or a full http:// or https:// URL.');
                },
            ],
            'cta_enabled' => ['nullable', 'boolean'],
            'overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'text_align' => ['nullable', 'string', 'in:left,center'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $raw = json_decode($this->getContent(), true);
            if (is_array($raw)) {
                $rawCta = $raw['ctaUrl'] ?? $raw['cta_url'] ?? null;
                if (is_string($rawCta) && strlen($rawCta) > 0 && trim($rawCta) === '') {
                    $validator->errors()->add('cta_url', 'The CTA URL may not be whitespace only.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title_line_1.required' => 'Title line 1 is required.',
            'title_line_1.max' => 'Title line 1 may not be greater than 60 characters.',
            'title_line_2.required' => 'Title line 2 is required.',
            'title_line_2.max' => 'Title line 2 may not be greater than 60 characters.',
            'badge_text.max' => 'Badge text may not be greater than 60 characters.',
            'description.max' => 'Description may not be greater than 250 characters.',
            'cta_text.max' => 'CTA button text may not be greater than 30 characters.',
            'overlay_opacity.min' => 'Overlay opacity must be between 0 and 100.',
            'overlay_opacity.max' => 'Overlay opacity must be between 0 and 100.',
            'text_align.in' => 'Text align must be either left or center.',
        ];
    }
}
