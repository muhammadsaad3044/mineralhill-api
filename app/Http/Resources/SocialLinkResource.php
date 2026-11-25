<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'label' => $this->label ?: $this->platform,
            'url' => $this->url,
            'enabled' => (bool) $this->enabled,
            'sortOrder' => (int) $this->sort_order,
        ];
    }
}
