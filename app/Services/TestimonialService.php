<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TestimonialService
{
    public const CACHE_ALL_KEY = 'public_testimonials_all';
    public const CACHE_FEATURED_KEY = 'public_testimonials_featured';
    public const CACHE_TTL_SECONDS = 3600;

    /**
     * Get published testimonials for public storefront with caching.
     */
    public function getPublicTestimonials(bool $featuredOnly = false): Collection
    {
        $cacheKey = $featuredOnly ? self::CACHE_FEATURED_KEY : self::CACHE_ALL_KEY;

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($featuredOnly) {
            $query = Testimonial::query()
                ->whereRaw('LOWER(status) = ?', ['published']);

            if ($featuredOnly) {
                $query->where('featured', true);
            } else {
                // Order by featured first, then newest
                $query->orderByDesc('featured')->orderByDesc('reviewed_at')->orderByDesc('id');
            }

            return $query->get();
        });
    }

    /**
     * Get testimonials for admin panel with search, filters, sorting, stats and distribution.
     */
    public function getAdminList(array $params = []): array
    {
        $search = trim($params['search'] ?? '');
        $status = trim($params['status'] ?? 'All');
        $rating = $params['rating'] ?? 'All';
        $sortBy = $params['sortBy'] ?? $params['sort_by'] ?? 'newest';
        $page = (int) ($params['page'] ?? 1);
        $perPage = (int) ($params['per_page'] ?? 100); // default large or paginated

        $allQuery = Testimonial::query();

        // Calculate summary metrics across ALL records
        $allRecords = (clone $allQuery)->get();
        $total = $allRecords->count();
        $published = $allRecords->where(fn ($t) => strtolower($t->status) === 'published')->count();
        $pending = $allRecords->where(fn ($t) => strtolower($t->status) === 'pending')->count();
        $hidden = $allRecords->where(fn ($t) => strtolower($t->status) === 'hidden')->count();

        $avgRating = $total > 0 ? round($allRecords->avg('rating'), 1) : 5.0;

        $distribution = collect([5, 4, 3, 2, 1])->map(function ($stars) use ($allRecords, $total) {
            $count = $allRecords->where('rating', $stars)->count();
            $percentage = $total > 0 ? (int) round(($count / $total) * 100) : 0;
            return [
                'stars' => $stars,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->values()->toArray();

        // Now apply filters for display
        $filteredQuery = Testimonial::query();

        if ($search !== '') {
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location_or_role', 'like', "%{$search}%")
                    ->orWhere('product', 'like', "%{$search}%")
                    ->orWhere('review', 'like', "%{$search}%");
            });
        }

        if ($status !== 'All' && $status !== '') {
            $filteredQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
        }

        if ($rating !== 'All' && $rating !== '') {
            $filteredQuery->where('rating', (int) $rating);
        }

        switch ($sortBy) {
            case 'oldest':
                $filteredQuery->orderBy('reviewed_at', 'asc')->orderBy('id', 'asc');
                break;
            case 'highest':
                $filteredQuery->orderByDesc('rating')->orderByDesc('id');
                break;
            case 'lowest':
                $filteredQuery->orderBy('rating', 'asc')->orderBy('id', 'asc');
                break;
            case 'newest':
            default:
                $filteredQuery->orderByDesc('reviewed_at')->orderByDesc('id');
                break;
        }

        $items = $filteredQuery->paginate($perPage, ['*'], 'page', $page);

        return [
            'testimonials' => $items->items(),
            'stats' => [
                'total' => $total,
                'published' => $published,
                'pending' => $pending,
                'hidden' => $hidden,
                'averageRating' => (string) number_format($avgRating, 1),
                'distribution' => $distribution,
            ],
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'perPage' => $items->perPage(),
                'total' => $items->total(),
            ],
        ];
    }

    /**
     * Create testimonial with optional avatar upload.
     */
    public function create(array $data, mixed $avatarInput = null): Testimonial
    {
        $avatarPath = null;

        if ($avatarInput instanceof UploadedFile) {
            $avatarPath = $this->handleAvatarUpload($avatarInput);
        } elseif (is_string($avatarInput) && trim($avatarInput) !== '') {
            $avatarPath = trim($avatarInput);
        }

        return Testimonial::create([
            'name' => trim($data['name']),
            'location_or_role' => trim($data['location_or_role'] ?? $data['role'] ?? 'Verified Buyer'),
            'avatar_path' => $avatarPath,
            'rating' => (int) $data['rating'],
            'product' => trim($data['product']),
            'review' => trim($data['review']),
            'reviewed_at' => $data['reviewed_at'] ?? $data['date'] ?? now()->toDateString(),
            'status' => strtolower(trim($data['status'] ?? 'published')),
            'featured' => isset($data['featured']) ? (bool) $data['featured'] : false,
        ]);
    }

    /**
     * Update testimonial with optional avatar upload/replacement.
     */
    public function update(Testimonial $testimonial, array $data, mixed $avatarInput = null): Testimonial
    {
        $payload = [];

        if (isset($data['name'])) $payload['name'] = trim($data['name']);
        if (isset($data['location_or_role']) || isset($data['role'])) {
            $payload['location_or_role'] = trim($data['location_or_role'] ?? $data['role']);
        }
        if (isset($data['rating'])) $payload['rating'] = (int) $data['rating'];
        if (isset($data['product'])) $payload['product'] = trim($data['product']);
        if (isset($data['review'])) $payload['review'] = trim($data['review']);
        if (isset($data['reviewed_at']) || isset($data['date'])) {
            $payload['reviewed_at'] = $data['reviewed_at'] ?? $data['date'];
        }
        if (isset($data['status'])) $payload['status'] = strtolower(trim($data['status']));
        if (isset($data['featured'])) $payload['featured'] = (bool) $data['featured'];

        // Handle avatar upload or removal
        if ($avatarInput instanceof UploadedFile) {
            $this->deleteOldAvatar($testimonial);
            $payload['avatar_path'] = $this->handleAvatarUpload($avatarInput);
        } elseif (is_string($avatarInput)) {
            if ($avatarInput === '') {
                $this->deleteOldAvatar($testimonial);
                $payload['avatar_path'] = null;
            } else {
                $payload['avatar_path'] = trim($avatarInput);
            }
        }

        $testimonial->update($payload);

        return $testimonial->fresh();
    }

    /**
     * Delete testimonial and clean up local avatar if present.
     */
    public function delete(Testimonial $testimonial): void
    {
        $this->deleteOldAvatar($testimonial);
        $testimonial->delete();
    }

    /**
     * Bulk update status for multiple testimonials.
     */
    public function bulkStatus(array $ids, string $status): int
    {
        return Testimonial::whereIn('id', $ids)->update([
            'status' => strtolower(trim($status)),
        ]);
    }

    /**
     * Bulk delete testimonials.
     */
    public function bulkDelete(array $ids): int
    {
        $records = Testimonial::whereIn('id', $ids)->get();
        foreach ($records as $item) {
            $this->deleteOldAvatar($item);
            $item->delete();
        }

        return $records->count();
    }

    protected function handleAvatarUpload(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'avatar_' . Str::random(12) . '.' . $extension;
        $relativePath = 'avatars/' . $filename;

        if ($extension === 'svg') {
            $rawContent = file_get_contents($file->getRealPath());
            $sanitized = SvgSanitizer::sanitize($rawContent);
            if ($sanitized === false) {
                throw ValidationException::withMessages([
                    'avatar' => ['The uploaded SVG avatar contains invalid or insecure content and was rejected.'],
                ]);
            }
            Storage::disk('public')->put($relativePath, $sanitized);
        } else {
            Storage::disk('public')->putFileAs('avatars', $file, $filename);
        }

        return $relativePath;
    }

    protected function deleteOldAvatar(Testimonial $testimonial): void
    {
        if ($testimonial->avatar_path && ! filter_var($testimonial->avatar_path, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($testimonial->avatar_path)) {
                Storage::disk('public')->delete($testimonial->avatar_path);
            }
        }
    }
}
