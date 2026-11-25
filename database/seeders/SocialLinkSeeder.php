<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = [
            [
                'platform' => 'Facebook',
                'label' => 'Facebook',
                'url' => 'https://facebook.com/mineralhillshilajit',
                'enabled' => true,
                'sort_order' => 1,
            ],
            [
                'platform' => 'Instagram',
                'label' => 'Instagram',
                'url' => 'https://instagram.com/mineralhillshilajit',
                'enabled' => true,
                'sort_order' => 2,
            ],
            [
                'platform' => 'YouTube',
                'label' => 'YouTube',
                'url' => 'https://youtube.com/@mineralhillshilajit',
                'enabled' => true,
                'sort_order' => 3,
            ],
            [
                'platform' => 'TikTok',
                'label' => 'TikTok',
                'url' => 'https://tiktok.com/@mineralhillshilajit',
                'enabled' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($links as $link) {
            SocialLink::firstOrCreate(
                ['platform' => $link['platform']],
                $link
            );
        }
    }
}
