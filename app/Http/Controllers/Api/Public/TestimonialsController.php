<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonialResource;
use App\Services\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialsController extends Controller
{
    public function __construct(
        protected TestimonialService $testimonialService
    ) {}

    /**
     * Public endpoint: Get published testimonials for landing page.
     */
    public function index(Request $request): JsonResponse
    {
        $featuredOnly = $request->boolean('featured', false);
        $testimonials = $this->testimonialService->getPublicTestimonials($featuredOnly);

        return response()->json([
            'success' => true,
            'data' => TestimonialResource::collection($testimonials),
        ]);
    }
}
