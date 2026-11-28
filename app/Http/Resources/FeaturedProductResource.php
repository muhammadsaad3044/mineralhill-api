<?php

namespace App\Http\Resources;

use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('*admin*');

        // Rating calculation
        $isManual = ($this->rating_mode ?? 'auto') === 'manual';
        if ($isManual) {
            $ratingVal = $this->rating_value !== null ? round((float) $this->rating_value, 1) : null;
            $ratingCnt = $this->rating_count !== null ? (int) $this->rating_count : null;
        } else {
            $published = Testimonial::whereRaw('LOWER(status) = ?', ['published'])->get();
            $ratingCnt = $published->count();
            $ratingVal = $ratingCnt > 0 ? round((float) $published->avg('rating'), 1) : 0.0;
        }

        // Sizes: public gets enabled only, admin gets all
        $sizesQuery = ProductSize::query()->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        if (! $isAdmin) {
            $sizesQuery->where('enabled', true);
        }
        $sizes = $sizesQuery->get();

        // Images: ordered by sort_order
        $images = ProductImage::query()->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        $data = [
            'id' => (int) $this->id,
            'eyebrowText' => $this->eyebrow_text,
            'titleLine1' => $this->title_line_1,
            'titleLine2' => $this->title_line_2,
            'description' => $this->description,
            'currencySymbol' => $this->currency_symbol,
            'ratingMode' => $this->rating_mode,
            'rating' => [
                'value' => $ratingVal,
                'count' => $ratingCnt,
            ],
            'ctaText' => $this->cta_text,
            'whatsappMessageTemplate' => $this->whatsapp_message_template,
            'showQuantitySelector' => (bool) $this->show_quantity_selector,
            'isActive' => (bool) $this->is_active,
            'sizes' => ProductSizeResource::collection($sizes),
            'images' => ProductImageResource::collection($images),
        ];

        if ($isAdmin) {
            $data['manualRatingValue'] = $this->rating_value !== null ? (float) $this->rating_value : null;
            $data['manualRatingCount'] = $this->rating_count !== null ? (int) $this->rating_count : null;
            $data['createdAt'] = $this->created_at?->toISOString();
            $data['updatedAt'] = $this->updated_at?->toISOString();
        }

        return $data;
    }
}
