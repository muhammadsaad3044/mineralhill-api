<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderProductImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:product_images,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'The image IDs list is required for reordering.',
            'ids.*.exists' => 'One or more specified image IDs do not exist.',
        ];
    }
}
