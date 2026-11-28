<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderProductSizesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:product_sizes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'The size IDs list is required for reordering.',
            'ids.*.exists' => 'One or more specified size IDs do not exist.',
        ];
    }
}
