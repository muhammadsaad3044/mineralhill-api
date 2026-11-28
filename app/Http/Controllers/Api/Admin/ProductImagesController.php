<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderProductImagesRequest;
use App\Http\Requests\StoreProductImageRequest;
use App\Http\Requests\UpdateProductImageRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductImagesController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * List all product images for admin panel.
     */
    public function index(): JsonResponse
    {
        $images = $this->productService->getImages();

        return response()->json([
            'success' => true,
            'data' => ProductImageResource::collection($images),
        ]);
    }

    /**
     * Upload a new product image.
     */
    public function store(StoreProductImageRequest $request): JsonResponse
    {
        $image = $this->productService->uploadImage(
            $request->file('image'),
            $request->input('alt_text')
        );

        return response()->json([
            'success' => true,
            'message' => 'Product image uploaded successfully.',
            'data' => new ProductImageResource($image),
        ], 201);
    }

    /**
     * Update image metadata (alt text).
     */
    public function update(UpdateProductImageRequest $request, int $id): JsonResponse
    {
        $image = ProductImage::findOrFail($id);
        $updated = $this->productService->updateImage($image, $request->input('alt_text'));

        return response()->json([
            'success' => true,
            'message' => 'Product image updated successfully.',
            'data' => new ProductImageResource($updated),
        ]);
    }

    /**
     * Delete an uploaded product image.
     */
    public function destroy(int $id): JsonResponse
    {
        $image = ProductImage::findOrFail($id);
        $this->productService->deleteImage($image);

        return response()->json([
            'success' => true,
            'message' => 'Product image deleted successfully.',
        ]);
    }

    /**
     * Reorder product gallery images.
     */
    public function reorder(ReorderProductImagesRequest $request): JsonResponse
    {
        $images = $this->productService->reorderImages($request->validated('ids'));

        return response()->json([
            'success' => true,
            'message' => 'Product images reordered successfully.',
            'data' => ProductImageResource::collection($images),
        ]);
    }
}
