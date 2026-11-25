<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderAccountLinksRequest;
use App\Http\Requests\StoreAccountLinkRequest;
use App\Http\Requests\UpdateAccountLinkRequest;
use App\Http\Resources\AccountLinkResource;
use App\Models\AccountLink;
use App\Services\AccountLinkService;
use Illuminate\Http\JsonResponse;

class AccountLinksController extends Controller
{
    public function __construct(
        protected AccountLinkService $accountService
    ) {}

    /**
     * List all account/contact links.
     */
    public function index(): JsonResponse
    {
        $links = $this->accountService->getAll();

        return response()->json([
            'success' => true,
            'data' => AccountLinkResource::collection($links),
        ]);
    }

    /**
     * Create a new account/contact link.
     */
    public function store(StoreAccountLinkRequest $request): JsonResponse
    {
        $link = $this->accountService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account link created successfully.',
            'data' => new AccountLinkResource($link),
        ], 201);
    }

    /**
     * Update an existing account/contact link.
     */
    public function update(UpdateAccountLinkRequest $request, int $id): JsonResponse
    {
        $link = AccountLink::findOrFail($id);
        $updated = $this->accountService->update($link, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account link updated successfully.',
            'data' => new AccountLinkResource($updated),
        ]);
    }

    /**
     * Delete an account/contact link.
     */
    public function destroy(int $id): JsonResponse
    {
        $link = AccountLink::findOrFail($id);
        $this->accountService->delete($link);

        return response()->json([
            'success' => true,
            'message' => 'Account link deleted successfully.',
            'id' => $id,
        ]);
    }

    /**
     * Reorder account/contact links.
     */
    public function reorder(ReorderAccountLinksRequest $request): JsonResponse
    {
        $links = $this->accountService->reorder($request->input('ids'));

        return response()->json([
            'success' => true,
            'message' => 'Account links reordered successfully.',
            'data' => AccountLinkResource::collection($links),
        ]);
    }
}
