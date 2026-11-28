<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSizeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'label' => $this->label,
            'price' => (float) $this->price,
            'compareAtPrice' => $this->compare_at_price !== null ? (float) $this->compare_at_price : null,
            'enabled' => (bool) $this->enabled,
            'isDefault' => (bool) $this->is_default,
            'sortOrder' => (int) $this->sort_order,
        ];
    }
}
