<?php

namespace App\Http\Resources;

use App\Models\HeroFeature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HeroSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bgUrl = null;
        if ($this->background_image_path) {
            $bgUrl = filter_var($this->background_image_path, FILTER_VALIDATE_URL)
                ? $this->background_image_path
                : url(Storage::url($this->background_image_path));
        }

        $bgMobileUrl = null;
        if ($this->background_image_mobile_path) {
            $bgMobileUrl = filter_var($this->background_image_mobile_path, FILTER_VALIDATE_URL)
                ? $this->background_image_mobile_path
                : url(Storage::url($this->background_image_mobile_path));
        }

        $isPublic = ! $request->is('*admin*');
        $featuresQuery = HeroFeature::orderBy('sort_order', 'asc')->orderBy('id', 'asc');

        if ($isPublic) {
            $featuresQuery->where('enabled', true);
        }

        return [
            'id' => $this->id,
            'badgeText' => $this->badge_text,
            'titleLine1' => $this->title_line_1,
            'titleLine2' => $this->title_line_2,
            'description' => $this->description,
            'ctaText' => $this->cta_text,
            'ctaUrl' => $this->cta_url,
            'ctaEnabled' => (bool) $this->cta_enabled,
            'backgroundImage' => $bgUrl,
            'backgroundImageMobile' => $bgMobileUrl,
            'overlayOpacity' => (int) $this->overlay_opacity,
            'textAlign' => $this->text_align ?? 'left',
            'isActive' => (bool) $this->is_active,
            'features' => HeroFeatureResource::collection($featuresQuery->get()),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
