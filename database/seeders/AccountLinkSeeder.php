<?php

namespace Database\Seeders;

use App\Models\AccountLink;
use Illuminate\Database\Seeder;

class AccountLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = [
            [
                'type' => 'Email',
                'label' => 'Customer Support Email',
                'value' => 'support@mineralhillshilajit.com',
                'enabled' => true,
                'sort_order' => 1,
            ],
            [
                'type' => 'Phone',
                'label' => 'Direct Phone Helpline',
                'value' => '+92 300 1234567',
                'enabled' => true,
                'sort_order' => 2,
            ],
            [
                'type' => 'Marketplace',
                'label' => 'Daraz Flagship Store',
                'value' => 'https://daraz.pk/shop/mineral-hill-shilajit',
                'enabled' => true,
                'sort_order' => 3,
            ],
            [
                'type' => 'Google Maps',
                'label' => 'Depot & Extraction Facility',
                'value' => 'https://maps.google.com/?q=Skardu+Himalayas',
                'enabled' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($links as $link) {
            AccountLink::firstOrCreate(
                ['label' => $link['label']],
                $link
            );
        }
    }
}
