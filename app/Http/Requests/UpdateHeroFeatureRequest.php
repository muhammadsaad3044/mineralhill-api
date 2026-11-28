<?php

namespace App\Http\Requests;

use App\Models\HeroFeature;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sortOrder') && ! $this->has('sort_order')) {
            $this->merge(['sort_order' => $this->input('sortOrder')]);
        }
    }

    public function rules(): array
    {
        return [
            'icon' => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', HeroFeature::ALLOWED_ICONS),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:30'],
            'subtitle' => ['sometimes', 'required', 'string', 'max:30'],
            'enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'icon.in' => 'Icon must be one of: ' . implode(', ', HeroFeature::ALLOWED_ICONS) . '.',
            'title.max' => 'Feature title may not be greater than 30 characters.',
            'subtitle.max' => 'Feature subtitle may not be greater than 30 characters.',
        ];
    }
}
