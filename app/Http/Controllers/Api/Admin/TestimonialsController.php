<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteTestimonialRequest;
use App\Http\Requests\BulkStatusTestimonialRequest;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialsController extends Controller
{
    public function __construct(
        protected TestimonialService $testimonialService
    ) {}

    /**
     * List testimonials with filtering, search, sorting, and statistical metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->testimonialService->getAdminList($request->all());

        return response()->json([
            'success' => true,
            'testimonials' => TestimonialResource::collection($result['testimonials']),
            'stats' => $result['stats'],
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * Get a single testimonial.
     */
    public function show(int $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TestimonialResource($testimonial),
        ]);
    }

    /**
     * Create a new testimonial.
     */
    public function store(StoreTestimonialRequest $request): JsonResponse
    {
        $avatarInput = $request->hasFile('avatar') ? $request->file('avatar') : $request->input('avatar');
        $testimonial = $this->testimonialService->create($request->validated(), $avatarInput);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully.',
            'data' => new TestimonialResource($testimonial),
        ], 201);
    }

    /**
     * Update an existing testimonial.
     */
    public function update(UpdateTestimonialRequest $request, int $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $avatarInput = $request->hasFile('avatar') ? $request->file('avatar') : $request->input('avatar');

        $updated = $this->testimonialService->update($testimonial, $request->validated(), $avatarInput);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully.',
            'data' => new TestimonialResource($updated),
        ]);
    }

    /**
     * Delete a testimonial.
     */
    public function destroy(int $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->testimonialService->delete($testimonial);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully.',
            'id' => $id,
        ]);
    }

    /**
     * Bulk update status.
     */
    public function bulkStatus(BulkStatusTestimonialRequest $request): JsonResponse
    {
        $count = $this->testimonialService->bulkStatus(
            $request->input('ids'),
            $request->input('status')
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully updated status for {$count} testimonials.",
            'updatedCount' => $count,
        ]);
    }

    /**
     * Bulk delete testimonials.
     */
    public function bulkDelete(BulkDeleteTestimonialRequest $request): JsonResponse
    {
        $count = $this->testimonialService->bulkDelete($request->input('ids'));

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} testimonials.",
            'deletedCount' => $count,
        ]);
    }
}
