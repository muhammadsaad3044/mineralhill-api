<?php

namespace App\Http\Requests;

use App\Models\HeroFeature;
use Illuminate\Foundation\Http\FormRequest;

class StoreHeroFeatureRequest extends FormRequest
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
                'required',
                'string',
                'in:' . implode(',', HeroFeature::ALLOWED_ICONS),
            ],
            'title' => ['required', 'string', 'max:30'],
            'subtitle' => ['required', 'string', 'max:30'],
            'enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (HeroFeature::count() >= HeroFeature::MAX_ITEMS) {
                $validator->errors()->add('feature', 'Maximum of 6 hero features allowed.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'icon.in' => 'Icon must be one of: ' . implode(', ', HeroFeature::ALLOWED_ICONS) . '.',
            'title.required' => 'Feature title is required.',
            'title.max' => 'Feature title may not be greater than 30 characters.',
            'subtitle.required' => 'Feature subtitle is required.',
            'subtitle.max' => 'Feature subtitle may not be greater than 30 characters.',
        ];
    }
}
