<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('compareAtPrice')) $merge['compare_at_price'] = $this->input('compareAtPrice');
        if ($this->has('isDefault')) $merge['is_default'] = $this->input('isDefault');
        if ($this->has('sortOrder')) $merge['sort_order'] = $this->input('sortOrder');

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:20', 'unique:product_sizes,label'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price'],
            'enabled' => ['boolean'],
            'is_default' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Size label is required (e.g. 15g).',
            'label.unique' => 'A size with this label already exists.',
            'price.required' => 'Price is required.',
            'price.min' => 'Price must be at least 0.01.',
            'price.max' => 'Price cannot exceed 999,999.',
            'compare_at_price.gt' => 'Compare-at price must be greater than the sale price.',
        ];
    }
}
