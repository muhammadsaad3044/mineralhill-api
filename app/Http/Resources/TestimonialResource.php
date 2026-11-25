<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatarUrl = null;
        if ($this->avatar_path) {
            $avatarUrl = filter_var($this->avatar_path, FILTER_VALIDATE_URL)
                ? $this->avatar_path
                : url(Storage::url($this->avatar_path));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->location_or_role,
            'locationOrRole' => $this->location_or_role,
            'avatar' => $avatarUrl ?: '',
            'rating' => (int) $this->rating,
            'product' => $this->product,
            'review' => $this->review,
            'date' => $this->reviewed_at ? $this->reviewed_at->format('Y-m-d') : ($this->created_at ? $this->created_at->format('Y-m-d') : null),
            'status' => ucfirst(strtolower($this->status)), // e.g. Published, Pending, Hidden
            'featured' => (bool) $this->featured,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
