<?php

namespace App\Services;

use App\Models\FeaturedProduct;
use App\Models\ProductImage;
use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public const CACHE_KEY = 'public_featured_product';
    public const CACHE_TTL_SECONDS = 3600;

    public const DEFAULT_PRODUCT = [
        'eyebrow_text' => 'PURE HIMALAYAN POWER',
        'title_line_1' => 'MINERAL HILL',
        'title_line_2' => 'SHILAJIT',
        'description' => 'Pure Himalayan Shilajit, rich in essential minerals, crafted to support your energy, stamina and overall wellness — naturally.',
        'currency_symbol' => '$',
        'rating_mode' => 'auto',
        'rating_value' => null,
        'rating_count' => null,
        'cta_text' => 'Shop Now',
        'whatsapp_message_template' => "Hello! I would like to order:\nProduct: {product}\nSize: {size}\nQuantity: {quantity}\nPrice: {currency}{price} each\nTotal: {currency}{total}\nPlease confirm availability and delivery details.",
        'show_quantity_selector' => true,
        'is_active' => true,
    ];

    public const DEFAULT_SIZES = [
        [
            'label' => '15g',
            'price' => 208.00,
            'compare_at_price' => 308.00,
            'enabled' => true,
            'is_default' => true,
            'sort_order' => 1,
        ],
        [
            'label' => '30g',
            'price' => 360.00,
            'compare_at_price' => 499.00,
            'enabled' => true,
            'is_default' => false,
            'sort_order' => 2,
        ],
        [
            'label' => '50g',
            'price' => 540.00,
            'compare_at_price' => 750.00,
            'enabled' => true,
            'is_default' => false,
            'sort_order' => 3,
        ],
        [
            'label' => '100g',
            'price' => 980.00,
            'compare_at_price' => 1350.00,
            'enabled' => true,
            'is_default' => false,
            'sort_order' => 4,
        ],
    ];

    /**
     * Clear cached public product payload.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get or create the single FeaturedProduct record.
     */
    public function getProductRecord(): FeaturedProduct
    {
        $product = FeaturedProduct::first();

        if (! $product) {
            $product = FeaturedProduct::create(self::DEFAULT_PRODUCT);
            $this->ensureDefaultSizes();
        }

        return $product;
    }

    /**
     * Ensure default sizes exist if table is empty.
     */
    public function ensureDefaultSizes(): void
    {
        if (ProductSize::count() === 0) {
            foreach (self::DEFAULT_SIZES as $size) {
                ProductSize::create($size);
            }
        }
    }

    /**
     * Get public product payload (cached).
     */
    public function getPublicProduct(): FeaturedProduct
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->getProductRecord();
        });
    }

    /**
     * Update product details.
     */
    public function updateProduct(array $data): FeaturedProduct
    {
        $product = $this->getProductRecord();

        $payload = [];
        if (array_key_exists('eyebrow_text', $data)) $payload['eyebrow_text'] = $data['eyebrow_text'];
        if (array_key_exists('title_line_1', $data)) $payload['title_line_1'] = trim($data['title_line_1']);
        if (array_key_exists('title_line_2', $data)) $payload['title_line_2'] = trim($data['title_line_2']);
        if (array_key_exists('description', $data)) $payload['description'] = $data['description'];
        if (array_key_exists('currency_symbol', $data)) $payload['currency_symbol'] = trim($data['currency_symbol'] ?? '$');
        if (array_key_exists('rating_mode', $data)) $payload['rating_mode'] = $data['rating_mode'];
        if (array_key_exists('rating_value', $data)) $payload['rating_value'] = $data['rating_value'];
        if (array_key_exists('rating_count', $data)) $payload['rating_count'] = $data['rating_count'];
        if (array_key_exists('cta_text', $data)) $payload['cta_text'] = $data['cta_text'];
        if (array_key_exists('whatsapp_message_template', $data)) $payload['whatsapp_message_template'] = $data['whatsapp_message_template'];
        if (array_key_exists('show_quantity_selector', $data)) $payload['show_quantity_selector'] = (bool) $data['show_quantity_selector'];
        if (array_key_exists('is_active', $data)) $payload['is_active'] = (bool) $data['is_active'];

        $product->update($payload);
        self::clearCache();

        return $product->fresh();
    }

    /**
     * Reset product, sizes and remove images.
     */
    public function resetProduct(): FeaturedProduct
    {
        // 1. Delete all images from storage and DB
        $images = ProductImage::all();
        foreach ($images as $img) {
            if ($img->path && ! filter_var($img->path, FILTER_VALIDATE_URL)) {
                if (Storage::disk('public')->exists($img->path)) {
                    Storage::disk('public')->delete($img->path);
                }
            }
            $img->delete();
        }

        // 2. Delete existing sizes and re-create defaults
        ProductSize::truncate();
        foreach (self::DEFAULT_SIZES as $size) {
            ProductSize::create($size);
        }

        // 3. Reset product text and settings
        $product = $this->getProductRecord();
        $product->update(self::DEFAULT_PRODUCT);

        self::clearCache();

        return $product->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SIZES MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function getSizes(): Collection
    {
        return ProductSize::query()->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
    }

    public function createSize(array $data): ProductSize
    {
        if (ProductSize::count() >= 8) {
            throw ValidationException::withMessages([
                'size' => ['Maximum of 8 product sizes allowed.'],
            ]);
        }

        $sortOrder = $data['sort_order'] ?? ((ProductSize::max('sort_order') ?? 0) + 1);
        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $isDefault = isset($data['is_default']) ? (bool) $data['is_default'] : false;

        // If first size or set as default among enabled
        if (ProductSize::where('enabled', true)->count() === 0) {
            $isDefault = true;
            $enabled = true;
        } elseif ($isDefault && $enabled) {
            ProductSize::query()->update(['is_default' => false]);
        } else {
            $isDefault = false;
        }

        $size = ProductSize::create([
            'label' => trim($data['label']),
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'enabled' => $enabled,
            'is_default' => $isDefault,
            'sort_order' => $sortOrder,
        ]);

        self::clearCache();

        return $size;
    }

    public function updateSize(ProductSize $size, array $data): ProductSize
    {
        $payload = [];
        if (isset($data['label'])) $payload['label'] = trim($data['label']);
        if (isset($data['price'])) $payload['price'] = $data['price'];
        if (array_key_exists('compare_at_price', $data)) $payload['compare_at_price'] = $data['compare_at_price'];
        if (isset($data['sort_order'])) $payload['sort_order'] = (int) $data['sort_order'];

        $newEnabled = isset($data['enabled']) ? (bool) $data['enabled'] : $size->enabled;
        $newDefault = isset($data['is_default']) ? (bool) $data['is_default'] : $size->is_default;

        // Disabling an enabled size
        if ($size->enabled && ! $newEnabled) {
            $otherEnabledCount = ProductSize::where('enabled', true)->where('id', '!=', $size->id)->count();
            if ($otherEnabledCount === 0) {
                throw ValidationException::withMessages([
                    'enabled' => ['Cannot disable the only enabled size. At least one enabled size must remain.'],
                ]);
            }

            // If default size is being disabled, move default to first remaining enabled size
            if ($size->is_default) {
                $newDefaultSize = ProductSize::where('enabled', true)
                    ->where('id', '!=', $size->id)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();

                if ($newDefaultSize) {
                    $newDefaultSize->update(['is_default' => true]);
                }
                $newDefault = false;
            }
        }

        // Setting a size as default
        if ($newDefault) {
            if (! $newEnabled) {
                throw ValidationException::withMessages([
                    'is_default' => ['A disabled size cannot be set as default.'],
                ]);
            }
            ProductSize::where('id', '!=', $size->id)->update(['is_default' => false]);
        }

        $payload['enabled'] = $newEnabled;
        $payload['is_default'] = $newDefault;

        $size->update($payload);
        self::clearCache();

        return $size->fresh();
    }

    public function deleteSize(ProductSize $size): void
    {
        // Check if last enabled size
        if ($size->enabled) {
            $otherEnabledCount = ProductSize::where('enabled', true)->where('id', '!=', $size->id)->count();
            if ($otherEnabledCount === 0) {
                throw ValidationException::withMessages([
                    'size' => ['Cannot delete the only enabled size. At least one enabled size must remain.'],
                ]);
            }

            // Move default if deleting the default size
            if ($size->is_default) {
                $newDefaultSize = ProductSize::where('enabled', true)
                    ->where('id', '!=', $size->id)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();

                if ($newDefaultSize) {
                    $newDefaultSize->update(['is_default' => true]);
                }
            }
        }

        $size->delete();
        self::clearCache();
    }

    public function setDefaultSize(ProductSize $size): ProductSize
    {
        if (! $size->enabled) {
            throw ValidationException::withMessages([
                'size' => ['Only an enabled size can be set as default.'],
            ]);
        }

        ProductSize::where('id', '!=', $size->id)->update(['is_default' => false]);
        $size->update(['is_default' => true]);

        self::clearCache();

        return $size->fresh();
    }

    public function reorderSizes(array $ids): Collection
    {
        foreach ($ids as $index => $id) {
            ProductSize::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        self::clearCache();

        return $this->getSizes();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMAGES MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function getImages(): Collection
    {
        return ProductImage::query()->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
    }

    public function uploadImage(UploadedFile $file, ?string $altText = null): ProductImage
    {
        if (ProductImage::count() >= 8) {
            throw ValidationException::withMessages([
                'image' => ['Maximum of 8 product images allowed.'],
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'product_' . Str::random(12) . '.' . $extension;
        $relativePath = 'products/' . $filename;

        Storage::disk('public')->putFileAs('products', $file, $filename);

        $sortOrder = (ProductImage::max('sort_order') ?? 0) + 1;

        $image = ProductImage::create([
            'path' => $relativePath,
            'alt_text' => $altText ? trim($altText) : null,
            'sort_order' => $sortOrder,
        ]);

        self::clearCache();

        return $image;
    }

    public function updateImage(ProductImage $image, ?string $altText): ProductImage
    {
        $image->update([
            'alt_text' => $altText ? trim($altText) : null,
        ]);

        self::clearCache();

        return $image->fresh();
    }

    public function deleteImage(ProductImage $image): void
    {
        if ($image->path && ! filter_var($image->path, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $image->delete();
        self::clearCache();
    }

    public function reorderImages(array $ids): Collection
    {
        foreach ($ids as $index => $id) {
            ProductImage::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        self::clearCache();

        return $this->getImages();
    }
}
