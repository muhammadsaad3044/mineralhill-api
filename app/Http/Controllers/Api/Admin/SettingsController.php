<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWhatsAppRequest;
use App\Http\Requests\UploadLogoRequest;
use App\Http\Resources\SiteSettingResource;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(
        protected SiteSettingService $settingService
    ) {}

    /**
     * Get current admin settings.
     */
    public function index(): JsonResponse
    {
        $setting = $this->settingService->getSettingsRecord();

        return response()->json([
            'success' => true,
            'data' => new SiteSettingResource($setting),
        ]);
    }

    /**
     * Update WhatsApp number, message, and enabled status.
     */
    public function updateWhatsApp(UpdateWhatsAppRequest $request): JsonResponse
    {
        $setting = $this->settingService->updateWhatsApp($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp configuration updated successfully.',
            'data' => (new SiteSettingResource($setting))->resolve()['whatsapp'],
        ]);
    }

    /**
     * Delete WhatsApp number (clears and disables).
     */
    public function deleteWhatsApp(): JsonResponse
    {
        $setting = $this->settingService->deleteWhatsApp();

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp number deleted and disabled successfully.',
            'data' => (new SiteSettingResource($setting))->resolve()['whatsapp'],
        ]);
    }

    /**
     * Upload brand logo.
     */
    public function uploadLogo(UploadLogoRequest $request): JsonResponse
    {
        $setting = $this->settingService->uploadLogo($request->file('logo'));

        return response()->json([
            'success' => true,
            'message' => 'Brand logo uploaded successfully.',
            'data' => (new SiteSettingResource($setting))->resolve(),
        ]);
    }

    /**
     * Remove brand logo.
     */
    public function removeLogo(): JsonResponse
    {
        $setting = $this->settingService->removeLogo();

        return response()->json([
            'success' => true,
            'message' => 'Brand logo removed successfully.',
            'data' => (new SiteSettingResource($setting))->resolve(),
        ]);
    }

    /**
     * Reset settings to defaults.
     */
    public function reset(): JsonResponse
    {
        $setting = $this->settingService->resetSettings();

        return response()->json([
            'success' => true,
            'message' => 'All settings reset to default successfully.',
            'data' => (new SiteSettingResource($setting))->resolve(),
        ]);
    }
}
