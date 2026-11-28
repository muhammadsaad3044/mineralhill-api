<?php

namespace Database\Seeders;

use App\Models\HeroFeature;
use App\Models\HeroSection;
use App\Services\HeroService;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HeroSection::firstOrCreate(
            ['id' => 1],
            HeroService::DEFAULT_HERO
        );

        if (HeroFeature::count() === 0) {
            foreach (HeroService::DEFAULT_FEATURES as $feature) {
                HeroFeature::create($feature);
            }
        }
    }
}
