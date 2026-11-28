<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHeroSectionRequest;
use App\Http\Requests\UploadHeroBackgroundRequest;
use App\Http\Resources\HeroSectionResource;
use App\Services\HeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroController extends Controller
{
    public function __construct(
        protected HeroService $heroService
    ) {}

    /**
     * Get hero section with all features (admin view).
     */
    public function show(): JsonResponse
    {
        $hero = $this->heroService->getHeroRecord();

        return response()->json([
            'success' => true,
            'data' => new HeroSectionResource($hero),
        ]);
    }

    /**
     * Update hero section content and settings.
     */
    public function update(UpdateHeroSectionRequest $request): JsonResponse
    {
        $hero = $this->heroService->updateHero($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hero section updated successfully.',
            'data' => new HeroSectionResource($hero),
        ]);
    }

    /**
     * Upload hero background image (desktop or mobile).
     */
    public function uploadBackground(UploadHeroBackgroundRequest $request): JsonResponse
    {
        $type = $request->input('type', 'desktop');
        $hero = $this->heroService->uploadBackground($request->file('image'), $type);

        return response()->json([
            'success' => true,
            'message' => 'Hero background image uploaded successfully.',
            'data' => new HeroSectionResource($hero),
        ]);
    }

    /**
     * Remove hero background image (desktop or mobile).
     */
    public function removeBackground(Request $request): JsonResponse
    {
        $type = $request->query('type', 'desktop');
        if (! in_array($type, ['desktop', 'mobile'], true)) {
            $type = 'desktop';
        }

        $hero = $this->heroService->removeBackground($type);

        return response()->json([
            'success' => true,
            'message' => 'Hero background image removed successfully.',
            'data' => new HeroSectionResource($hero),
        ]);
    }

    /**
     * Reset hero section and features to default state.
     */
    public function reset(): JsonResponse
    {
        $hero = $this->heroService->reset();

        return response()->json([
            'success' => true,
            'message' => 'Hero section and features reset to default successfully.',
            'data' => new HeroSectionResource($hero),
        ]);
    }
}
