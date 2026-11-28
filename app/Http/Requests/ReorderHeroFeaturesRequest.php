<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderHeroFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:hero_features,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'List of feature IDs is required for reordering.',
            'ids.*.exists' => 'One or more feature IDs do not exist.',
        ];
    }
}
