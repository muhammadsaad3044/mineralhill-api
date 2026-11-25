<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderSocialLinksRequest;
use App\Http\Requests\StoreSocialLinkRequest;
use App\Http\Requests\UpdateSocialLinkRequest;
use App\Http\Resources\SocialLinkResource;
use App\Models\SocialLink;
use App\Services\SocialLinkService;
use Illuminate\Http\JsonResponse;

class SocialLinksController extends Controller
{
    public function __construct(
        protected SocialLinkService $socialService
    ) {}

    /**
     * List all social links.
     */
    public function index(): JsonResponse
    {
        $links = $this->socialService->getAll();

        return response()->json([
            'success' => true,
            'data' => SocialLinkResource::collection($links),
        ]);
    }

    /**
     * Create a new social link.
     */
    public function store(StoreSocialLinkRequest $request): JsonResponse
    {
        $link = $this->socialService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Social link created successfully.',
            'data' => new SocialLinkResource($link),
        ], 201);
    }

    /**
     * Update an existing social link.
     */
    public function update(UpdateSocialLinkRequest $request, int $id): JsonResponse
    {
        $link = SocialLink::findOrFail($id);
        $updated = $this->socialService->update($link, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Social link updated successfully.',
            'data' => new SocialLinkResource($updated),
        ]);
    }

    /**
     * Delete a social link.
     */
    public function destroy(int $id): JsonResponse
    {
        $link = SocialLink::findOrFail($id);
        $this->socialService->delete($link);

        return response()->json([
            'success' => true,
            'message' => 'Social link deleted successfully.',
            'id' => $id,
        ]);
    }

    /**
     * Reorder social links.
     */
    public function reorder(ReorderSocialLinksRequest $request): JsonResponse
    {
        $links = $this->socialService->reorder($request->input('ids'));

        return response()->json([
            'success' => true,
            'message' => 'Social links reordered successfully.',
            'data' => SocialLinkResource::collection($links),
        ]);
    }
}
