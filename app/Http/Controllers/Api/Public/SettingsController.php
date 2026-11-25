<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteSettingResource;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(
        protected SiteSettingService $settingService
    ) {}

    /**
     * Public endpoint: Get site settings for landing page.
     */
    public function index(): JsonResponse
    {
        $setting = $this->settingService->getPublicSettings();

        return response()->json([
            'success' => true,
            'data' => new SiteSettingResource($setting),
        ]);
    }
}
