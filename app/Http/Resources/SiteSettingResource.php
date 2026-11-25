<?php

namespace App\Http\Resources;

use App\Models\AccountLink;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SiteSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logoUrl = null;
        if ($this->logo_path) {
            $logoUrl = filter_var($this->logo_path, FILTER_VALIDATE_URL)
                ? $this->logo_path
                : url(Storage::url($this->logo_path));
        }

        // Check if caller requested only enabled links (e.g. public storefront) or all (admin)
        $isPublic = ! $request->is('*admin*');

        $socialQuery = SocialLink::orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        $accountQuery = AccountLink::orderBy('sort_order', 'asc')->orderBy('id', 'asc');

        if ($isPublic) {
            $socialQuery->where('enabled', true);
            $accountQuery->where('enabled', true);
        }

        return [
            'logo' => $logoUrl,
            'whatsapp' => [
                'enabled' => (bool) $this->whatsapp_enabled,
                'phoneNumber' => $this->whatsapp_number ?: '',
                'defaultMessage' => $this->whatsapp_message ?: '',
            ],
            'socialLinks' => SocialLinkResource::collection($socialQuery->get()),
            'accountLinks' => AccountLinkResource::collection($accountQuery->get()),
        ];
    }
}
