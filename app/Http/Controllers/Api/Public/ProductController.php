<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeaturedProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Get featured product details for public storefront.
     */
    public function index(): JsonResponse
    {
        $product = $this->productService->getPublicProduct();

        return response()->json([
            'success' => true,
            'data' => (new FeaturedProductResource($product))->resolve(),
        ]);
    }
}
