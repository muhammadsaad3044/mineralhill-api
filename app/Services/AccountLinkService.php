<?php

namespace App\Services;

use App\Models\AccountLink;
use Illuminate\Database\Eloquent\Collection;

class AccountLinkService
{
    /**
     * Get all account links ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return AccountLink::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
    }

    /**
     * Create a new account link.
     */
    public function create(array $data): AccountLink
    {
        $maxOrder = (int) AccountLink::max('sort_order');

        return AccountLink::create([
            'type' => trim($data['type']),
            'label' => trim($data['label']),
            'value' => trim($data['value']),
            'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : true,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    /**
     * Update an existing account link.
     */
    public function update(AccountLink $link, array $data): AccountLink
    {
        $payload = [];
        if (isset($data['type'])) $payload['type'] = trim($data['type']);
        if (isset($data['label'])) $payload['label'] = trim($data['label']);
        if (isset($data['value'])) $payload['value'] = trim($data['value']);
        if (isset($data['enabled'])) $payload['enabled'] = (bool) $data['enabled'];

        $link->update($payload);

        return $link->fresh();
    }

    /**
     * Delete an account link.
     */
    public function delete(AccountLink $link): void
    {
        $link->delete();
    }

    /**
     * Reorder account links by an array of IDs.
     */
    public function reorder(array $ids): Collection
    {
        foreach ($ids as $index => $id) {
            AccountLink::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return $this->getAll();
    }
}
