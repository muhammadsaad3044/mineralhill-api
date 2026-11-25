<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Notice rule: Do NOT put the fake demo WhatsApp number in seeders; leave it empty if mockSettings.js has a sample number.
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'logo_path' => null,
                'whatsapp_number' => '',
                'whatsapp_message' => 'Hello Mineral Hill Shilajit! I would like to inquire about pure Himalayan Shilajit.',
                'whatsapp_enabled' => true,
            ]
        );
    }
}
