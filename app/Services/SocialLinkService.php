<?php

namespace App\Services;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Collection;

class SocialLinkService
{
    /**
     * Get all social links ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return SocialLink::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
    }

    /**
     * Create a new social link.
     */
    public function create(array $data): SocialLink
    {
        $maxOrder = (int) SocialLink::max('sort_order');

        return SocialLink::create([
            'platform' => trim($data['platform']),
            'label' => ! empty($data['label']) ? trim($data['label']) : trim($data['platform']),
            'url' => trim($data['url']),
            'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : true,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    /**
     * Update an existing social link.
     */
    public function update(SocialLink $link, array $data): SocialLink
    {
        $payload = [];
        if (isset($data['platform'])) $payload['platform'] = trim($data['platform']);
        if (isset($data['label'])) $payload['label'] = trim($data['label']);
        if (isset($data['url'])) $payload['url'] = trim($data['url']);
        if (isset($data['enabled'])) $payload['enabled'] = (bool) $data['enabled'];

        $link->update($payload);

        return $link->fresh();
    }

    /**
     * Delete a social link.
     */
    public function delete(SocialLink $link): void
    {
        $link->delete();
    }

    /**
     * Reorder social links by an array of IDs.
     */
    public function reorder(array $ids): Collection
    {
        foreach ($ids as $index => $id) {
            SocialLink::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return $this->getAll();
    }
}
