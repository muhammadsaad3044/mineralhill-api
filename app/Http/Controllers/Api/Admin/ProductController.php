<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFeaturedProductRequest;
use App\Http\Resources\FeaturedProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Get featured product details for admin panel.
     */
    public function show(): JsonResponse
    {
        $product = $this->productService->getProductRecord();

        return response()->json([
            'success' => true,
            'data' => (new FeaturedProductResource($product))->resolve(),
        ]);
    }

    /**
     * Update featured product copy and configuration.
     */
    public function update(UpdateFeaturedProductRequest $request): JsonResponse
    {
        $product = $this->productService->updateProduct($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Featured product updated successfully.',
            'data' => (new FeaturedProductResource($product))->resolve(),
        ]);
    }

    /**
     * Reset featured product, sizes and images to factory defaults.
     */
    public function reset(): JsonResponse
    {
        $product = $this->productService->resetProduct();

        return response()->json([
            'success' => true,
            'message' => 'Featured product reset to factory defaults successfully.',
            'data' => (new FeaturedProductResource($product))->resolve(),
        ]);
    }
}
