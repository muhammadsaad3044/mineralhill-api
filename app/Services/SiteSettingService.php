<?php

namespace App\Services;

use App\Models\AccountLink;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SiteSettingService
{
    public const CACHE_KEY = 'public_settings_cache';
    public const CACHE_TTL_SECONDS = 3600; // 1 hour (cleared automatically on change)

    /**
     * Get or create singleton site setting.
     */
    public function getSettingsRecord(): SiteSetting
    {
        return SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'logo_path' => null,
                'whatsapp_number' => '',
                'whatsapp_message' => 'Hello Mineral Hill Shilajit! I would like to inquire about pure Himalayan Shilajit.',
                'whatsapp_enabled' => true,
            ]
        );
    }

    /**
     * Get public settings with short-lived cache.
     */
    public function getPublicSettings(): SiteSetting
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->getSettingsRecord();
        });
    }

    /**
     * Update WhatsApp settings.
     */
    public function updateWhatsApp(array $data): SiteSetting
    {
        $setting = $this->getSettingsRecord();

        $phoneNumber = $data['phoneNumber'] ?? $data['whatsapp_number'] ?? $setting->whatsapp_number;
        $defaultMessage = $data['defaultMessage'] ?? $data['whatsapp_message'] ?? $setting->whatsapp_message;
        $enabled = isset($data['enabled'])
            ? (bool) $data['enabled']
            : (isset($data['whatsapp_enabled']) ? (bool) $data['whatsapp_enabled'] : $setting->whatsapp_enabled);

        $setting->update([
            'whatsapp_number' => $phoneNumber,
            'whatsapp_message' => $defaultMessage,
            'whatsapp_enabled' => $enabled,
        ]);

        return $setting->fresh();
    }

    /**
     * Delete WhatsApp number (clears and disables).
     */
    public function deleteWhatsApp(): SiteSetting
    {
        $setting = $this->getSettingsRecord();

        $setting->update([
            'whatsapp_number' => '',
            'whatsapp_enabled' => false,
        ]);

        return $setting->fresh();
    }

    /**
     * Upload and update brand logo.
     */
    public function uploadLogo(UploadedFile $file): SiteSetting
    {
        $setting = $this->getSettingsRecord();

        // 1. Delete old logo if present
        if ($setting->logo_path && ! filter_var($setting->logo_path, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'logo_' . Str::random(12) . '.' . $extension;
        $relativePath = 'brand/' . $filename;

        // 2. SVG Sanitization check
        if ($extension === 'svg') {
            $rawContent = file_get_contents($file->getRealPath());
            $sanitized = SvgSanitizer::sanitize($rawContent);

            if ($sanitized === false) {
                throw ValidationException::withMessages([
                    'logo' => ['The uploaded SVG file contains invalid or insecure content and was rejected.'],
                ]);
            }

            Storage::disk('public')->put($relativePath, $sanitized);
        } else {
            Storage::disk('public')->putFileAs('brand', $file, $filename);
        }

        $setting->update(['logo_path' => $relativePath]);

        return $setting->fresh();
    }

    /**
     * Remove brand logo.
     */
    public function removeLogo(): SiteSetting
    {
        $setting = $this->getSettingsRecord();

        if ($setting->logo_path && ! filter_var($setting->logo_path, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
        }

        $setting->update(['logo_path' => null]);

        return $setting->fresh();
    }

    /**
     * Reset all settings to default.
     */
    public function resetSettings(): SiteSetting
    {
        $setting = $this->getSettingsRecord();

        // Remove uploaded logo file if exists
        if ($setting->logo_path && ! filter_var($setting->logo_path, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
        }

        $setting->update([
            'logo_path' => null,
            'whatsapp_number' => '',
            'whatsapp_message' => 'Hello Mineral Hill Shilajit! I would like to inquire about pure Himalayan Shilajit.',
            'whatsapp_enabled' => true,
        ]);

        return $setting->fresh();
    }
}
