<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location_or_role' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable'], // can be uploaded file or URL string
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'product' => ['required', 'string', 'max:255'],
            'review' => ['required', 'string', 'min:20', 'max:300'],
            'reviewed_at' => ['nullable', 'date'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:published,pending,hidden,Published,Pending,Hidden'],
            'featured' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'review.min' => 'The review must be at least 20 characters.',
            'review.max' => 'The review may not be greater than 300 characters.',
            'rating.min' => 'Rating must be between 1 and 5 stars.',
            'rating.max' => 'Rating must be between 1 and 5 stars.',
        ];
    }
}
