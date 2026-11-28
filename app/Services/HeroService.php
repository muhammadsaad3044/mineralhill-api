<?php

namespace App\Services;

use App\Models\HeroFeature;
use App\Models\HeroSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HeroService
{
    public const CACHE_KEY = 'public_hero_cache';
    public const CACHE_TTL_SECONDS = 3600; // 1 hour (cleared automatically on change)

    public const DEFAULT_HERO = [
        'badge_text' => 'PURE • PREMIUM • AUTHENTIC',
        'title_line_1' => 'NATURAL POWER.',
        'title_line_2' => 'PURE SHILAJIT.',
        'description' => 'Premium Shilajit crafted for your everyday wellness & vitality.',
        'cta_text' => 'Shop Now',
        'cta_url' => '#shop',
        'cta_enabled' => true,
        'background_image_path' => null,
        'background_image_mobile_path' => null,
        'overlay_opacity' => 0,
        'text_align' => 'left',
        'is_active' => true,
    ];

    public const DEFAULT_FEATURES = [
        [
            'icon' => 'leaf',
            'title' => '100%',
            'subtitle' => 'PURE SHILAJIT',
            'enabled' => true,
            'sort_order' => 1,
        ],
        [
            'icon' => 'diamond',
            'title' => 'PREMIUM',
            'subtitle' => 'QUALITY',
            'enabled' => true,
            'sort_order' => 2,
        ],
        [
            'icon' => 'flask',
            'title' => 'LAB',
            'subtitle' => 'TESTED',
            'enabled' => true,
            'sort_order' => 3,
        ],
        [
            'icon' => 'shield',
            'title' => 'AUTHENTIC',
            'subtitle' => 'SOURCE',
            'enabled' => true,
            'sort_order' => 4,
        ],
    ];

    /**
     * Get or create the singleton hero section record.
     */
    public function getHeroRecord(): HeroSection
    {
        return HeroSection::firstOrCreate(
            ['id' => 1],
            self::DEFAULT_HERO
        );
    }

    /**
     * Get public hero content with short-lived cache.
     */
    public function getPublicHero(): HeroSection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->getHeroRecord();
        });
    }

    /**
     * Clear hero cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Update hero section content.
     */
    public function updateHero(array $data): HeroSection
    {
        $hero = $this->getHeroRecord();

        $attributes = [
            'title_line_1' => $data['title_line_1'] ?? $data['titleLine1'] ?? $hero->title_line_1,
            'title_line_2' => $data['title_line_2'] ?? $data['titleLine2'] ?? $hero->title_line_2,
            'badge_text' => array_key_exists('badge_text', $data) ? $data['badge_text'] : (array_key_exists('badgeText', $data) ? $data['badgeText'] : $hero->badge_text),
            'description' => array_key_exists('description', $data) ? $data['description'] : $hero->description,
            'cta_text' => array_key_exists('cta_text', $data) ? $data['cta_text'] : (array_key_exists('ctaText', $data) ? $data['ctaText'] : $hero->cta_text),
            'cta_url' => array_key_exists('cta_url', $data) ? $data['cta_url'] : (array_key_exists('ctaUrl', $data) ? $data['ctaUrl'] : $hero->cta_url),
            'cta_enabled' => isset($data['cta_enabled']) ? (bool) $data['cta_enabled'] : (isset($data['ctaEnabled']) ? (bool) $data['ctaEnabled'] : $hero->cta_enabled),
            'overlay_opacity' => isset($data['overlay_opacity']) ? (int) $data['overlay_opacity'] : (isset($data['overlayOpacity']) ? (int) $data['overlayOpacity'] : $hero->overlay_opacity),
            'text_align' => $data['text_align'] ?? $data['textAlign'] ?? $hero->text_align,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : (isset($data['isActive']) ? (bool) $data['isActive'] : $hero->is_active),
        ];

        $hero->update($attributes);
        $this->clearCache();

        return $hero->fresh();
    }

    /**
     * Upload hero background image (desktop or mobile).
     */
    public function uploadBackground(UploadedFile $file, string $type = 'desktop'): HeroSection
    {
        $hero = $this->getHeroRecord();
        $isMobile = strtolower($type) === 'mobile';
        $column = $isMobile ? 'background_image_mobile_path' : 'background_image_path';
        $currentPath = $hero->{$column};

        // 1. Delete previous file if exists on public disk
        if ($currentPath && ! filter_var($currentPath, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($currentPath)) {
                Storage::disk('public')->delete($currentPath);
            }
        }

        // 2. Store new file on public disk (hero folder)
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = ($isMobile ? 'hero_bg_mobile_' : 'hero_bg_') . Str::random(12) . '.' . $extension;
        $relativePath = 'hero/' . $filename;

        Storage::disk('public')->putFileAs('hero', $file, $filename);

        $hero->update([$column => $relativePath]);
        $this->clearCache();

        return $hero->fresh();
    }

    /**
     * Remove hero background image (desktop or mobile).
     */
    public function removeBackground(string $type = 'desktop'): HeroSection
    {
        $hero = $this->getHeroRecord();
        $isMobile = strtolower($type) === 'mobile';
        $column = $isMobile ? 'background_image_mobile_path' : 'background_image_path';
        $currentPath = $hero->{$column};

        if ($currentPath && ! filter_var($currentPath, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($currentPath)) {
                Storage::disk('public')->delete($currentPath);
            }
        }

        $hero->update([$column => null]);
        $this->clearCache();

        return $hero->fresh();
    }

    /**
     * Get all hero features.
     */
    public function getFeatures(): Collection
    {
        return HeroFeature::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
    }

    /**
     * Create a new hero feature.
     */
    public function createFeature(array $data): HeroFeature
    {
        if (HeroFeature::count() >= HeroFeature::MAX_ITEMS) {
            throw ValidationException::withMessages([
                'feature' => ['Maximum of 6 hero features allowed.'],
            ]);
        }

        $sortOrder = $data['sort_order'] ?? $data['sortOrder'] ?? ((HeroFeature::max('sort_order') ?? 0) + 1);

        $feature = HeroFeature::create([
            'icon' => $data['icon'],
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : true,
            'sort_order' => (int) $sortOrder,
        ]);

        $this->clearCache();

        return $feature;
    }

    /**
     * Update an existing hero feature.
     */
    public function updateFeature(HeroFeature $feature, array $data): HeroFeature
    {
        $attributes = [];

        if (array_key_exists('icon', $data)) {
            $attributes['icon'] = $data['icon'];
        }
        if (array_key_exists('title', $data)) {
            $attributes['title'] = $data['title'];
        }
        if (array_key_exists('subtitle', $data)) {
            $attributes['subtitle'] = $data['subtitle'];
        }
        if (array_key_exists('enabled', $data)) {
            $attributes['enabled'] = (bool) $data['enabled'];
        }
        if (array_key_exists('sort_order', $data) || array_key_exists('sortOrder', $data)) {
            $attributes['sort_order'] = (int) ($data['sort_order'] ?? $data['sortOrder']);
        }

        $feature->update($attributes);
        $this->clearCache();

        return $feature->fresh();
    }

    /**
     * Delete a hero feature.
     */
    public function deleteFeature(HeroFeature $feature): void
    {
        $feature->delete();
        $this->clearCache();
    }

    /**
     * Reorder hero features.
     */
    public function reorderFeatures(array $ids): Collection
    {
        foreach ($ids as $order => $id) {
            HeroFeature::where('id', $id)->update(['sort_order' => $order + 1]);
        }

        $this->clearCache();

        return $this->getFeatures();
    }

    /**
     * Reset hero section and features to default state.
     */
    public function reset(): HeroSection
    {
        $hero = $this->getHeroRecord();

        // 1. Delete uploaded files if present
        foreach (['background_image_path', 'background_image_mobile_path'] as $column) {
            $path = $hero->{$column};
            if ($path && ! filter_var($path, FILTER_VALIDATE_URL)) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        // 2. Reset hero section table
        $hero->update(self::DEFAULT_HERO);

        // 3. Reset hero features table
        HeroFeature::truncate();
        foreach (self::DEFAULT_FEATURES as $featureData) {
            HeroFeature::create($featureData);
        }

        $this->clearCache();

        return $hero->fresh();
    }
}
