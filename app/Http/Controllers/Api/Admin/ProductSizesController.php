<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderProductSizesRequest;
use App\Http\Requests\StoreProductSizeRequest;
use App\Http\Requests\UpdateProductSizeRequest;
use App\Http\Resources\ProductSizeResource;
use App\Models\ProductSize;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductSizesController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * List all sizes for admin panel.
     */
    public function index(): JsonResponse
    {
        $sizes = $this->productService->getSizes();

        return response()->json([
            'success' => true,
            'data' => ProductSizeResource::collection($sizes),
        ]);
    }

    /**
     * Create a new product size.
     */
    public function store(StoreProductSizeRequest $request): JsonResponse
    {
        $size = $this->productService->createSize($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product size created successfully.',
            'data' => new ProductSizeResource($size),
        ], 201);
    }

    /**
     * Update an existing product size.
     */
    public function update(UpdateProductSizeRequest $request, int $id): JsonResponse
    {
        $size = ProductSize::findOrFail($id);
        $updated = $this->productService->updateSize($size, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product size updated successfully.',
            'data' => new ProductSizeResource($updated),
        ]);
    }

    /**
     * Delete a product size.
     */
    public function destroy(int $id): JsonResponse
    {
        $size = ProductSize::findOrFail($id);
        $this->productService->deleteSize($size);

        return response()->json([
            'success' => true,
            'message' => 'Product size deleted successfully.',
        ]);
    }

    /**
     * Reorder product sizes.
     */
    public function reorder(ReorderProductSizesRequest $request): JsonResponse
    {
        $sizes = $this->productService->reorderSizes($request->validated('ids'));

        return response()->json([
            'success' => true,
            'message' => 'Product sizes reordered successfully.',
            'data' => ProductSizeResource::collection($sizes),
        ]);
    }

    /**
     * Set a specific size as the default selection.
     */
    public function setDefault(int $id): JsonResponse
    {
        $size = ProductSize::findOrFail($id);
        $updated = $this->productService->setDefaultSize($size);

        return response()->json([
            'success' => true,
            'message' => 'Default product size updated successfully.',
            'data' => new ProductSizeResource($updated),
        ]);
    }
}
