<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderHeroFeaturesRequest;
use App\Http\Requests\StoreHeroFeatureRequest;
use App\Http\Requests\UpdateHeroFeatureRequest;
use App\Http\Resources\HeroFeatureResource;
use App\Models\HeroFeature;
use App\Services\HeroService;
use Illuminate\Http\JsonResponse;

class HeroFeaturesController extends Controller
{
    public function __construct(
        protected HeroService $heroService
    ) {}

    /**
     * List all hero features (admin view).
     */
    public function index(): JsonResponse
    {
        $features = $this->heroService->getFeatures();

        return response()->json([
            'success' => true,
            'data' => HeroFeatureResource::collection($features),
        ]);
    }

    /**
     * Create a new hero feature.
     */
    public function store(StoreHeroFeatureRequest $request): JsonResponse
    {
        $feature = $this->heroService->createFeature($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hero feature created successfully.',
            'data' => new HeroFeatureResource($feature),
        ], 201);
    }

    /**
     * Update an existing hero feature.
     */
    public function update(UpdateHeroFeatureRequest $request, int $id): JsonResponse
    {
        $feature = HeroFeature::findOrFail($id);
        $updated = $this->heroService->updateFeature($feature, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hero feature updated successfully.',
            'data' => new HeroFeatureResource($updated),
        ]);
    }

    /**
     * Delete a hero feature.
     */
    public function destroy(int $id): JsonResponse
    {
        $feature = HeroFeature::findOrFail($id);
        $this->heroService->deleteFeature($feature);

        return response()->json([
            'success' => true,
            'message' => 'Hero feature deleted successfully.',
            'id' => $id,
        ]);
    }

    /**
     * Reorder hero features.
     */
    public function reorder(ReorderHeroFeaturesRequest $request): JsonResponse
    {
        $features = $this->heroService->reorderFeatures($request->input('ids'));

        return response()->json([
            'success' => true,
            'message' => 'Hero features reordered successfully.',
            'data' => HeroFeatureResource::collection($features),
        ]);
    }
}
