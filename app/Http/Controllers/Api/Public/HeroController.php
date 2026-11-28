<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroSectionResource;
use App\Services\HeroService;
use Illuminate\Http\JsonResponse;

class HeroController extends Controller
{
    public function __construct(
        protected HeroService $heroService
    ) {}

    /**
     * Get public hero section content with enabled features.
     */
    public function index(): JsonResponse
    {
        $hero = $this->heroService->getPublicHero();

        return response()->json([
            'success' => true,
            'data' => new HeroSectionResource($hero),
        ]);
    }
}
