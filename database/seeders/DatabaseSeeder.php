<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingsSeeder::class,
            HeroSeeder::class,
            SocialLinkSeeder::class,
            AccountLinkSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
