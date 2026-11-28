<?php

namespace Tests\Feature;

use App\Models\FeaturedProduct;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateAdmin(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $admin->createToken('admin-test-token')->plainTextToken;
    }

    protected function authenticateRegularUser(): string
    {
        $user = User::factory()->create(['is_admin' => false]);

        return $user->createToken('user-test-token')->plainTextToken;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. PUBLIC STOREFRONT ENDPOINT
    // ─────────────────────────────────────────────────────────────────────────

    public function test_public_product_endpoint_returns_expected_structure_and_enabled_sizes_only(): void
    {
        $service = app(ProductService::class);
        $service->resetProduct();

        // Add a disabled size
        ProductSize::create([
            'label' => 'Disabled Size',
            'price' => 150.00,
            'compare_at_price' => 200.00,
            'enabled' => false,
            'is_default' => false,
            'sort_order' => 99,
        ]);

        $response = $this->getJson('/api/v1/product');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'eyebrowText',
                    'titleLine1',
                    'titleLine2',
                    'description',
                    'currencySymbol',
                    'ratingMode',
                    'rating' => [
                        'value',
                        'count',
                    ],
                    'ctaText',
                    'whatsappMessageTemplate',
                    'showQuantitySelector',
                    'isActive',
                    'sizes' => [
                        '*' => [
                            'id',
                            'label',
                            'price',
                            'compareAtPrice',
                            'enabled',
                            'isDefault',
                            'sortOrder',
                        ],
                    ],
                    'images',
                ],
            ]);

        // Ensure no disabled sizes returned in public payload
        $sizes = $response->json('data.sizes');
        foreach ($sizes as $s) {
            $this->assertTrue($s['enabled']);
            $this->assertNotEquals('Disabled Size', $s['label']);
        }
    }

    public function test_rating_auto_mode_computes_average_and_count_from_published_testimonials(): void
    {
        $service = app(ProductService::class);
        $service->resetProduct();

        // Ensure product is in auto mode
        $product = FeaturedProduct::first();
        $product->update(['rating_mode' => 'auto']);

        // Clear existing testimonials
        Testimonial::truncate();

        // Create published testimonials (4 reviews: 5, 5, 5, 4 -> avg: 4.75 -> rounded to 4.8)
        Testimonial::create(['name' => 'A', 'location_or_role' => 'Lahore', 'rating' => 5, 'product' => 'P', 'review' => 'R', 'status' => 'published']);
        Testimonial::create(['name' => 'B', 'location_or_role' => 'Karachi', 'rating' => 5, 'product' => 'P', 'review' => 'R', 'status' => 'published']);
        Testimonial::create(['name' => 'C', 'location_or_role' => 'Islamabad', 'rating' => 5, 'product' => 'P', 'review' => 'R', 'status' => 'published']);
        Testimonial::create(['name' => 'D', 'location_or_role' => 'Peshawar', 'rating' => 4, 'product' => 'P', 'review' => 'R', 'status' => 'published']);
        // 1 pending testimonial (should NOT be counted)
        Testimonial::create(['name' => 'E', 'location_or_role' => 'Quetta', 'rating' => 1, 'product' => 'P', 'review' => 'R', 'status' => 'pending']);

        ProductService::clearCache();

        $response = $this->getJson('/api/v1/product');

        $response->assertStatus(200)
            ->assertJsonPath('data.rating.count', 4)
            ->assertJsonPath('data.rating.value', 4.8);
    }

    public function test_rating_manual_mode_uses_stored_values(): void
    {
        $service = app(ProductService::class);
        $service->resetProduct();

        $product = FeaturedProduct::first();
        $product->update([
            'rating_mode' => 'manual',
            'rating_value' => 4.9,
            'rating_count' => 88,
        ]);

        ProductService::clearCache();

        $response = $this->getJson('/api/v1/product');

        $response->assertStatus(200)
            ->assertJsonPath('data.rating.count', 88)
            ->assertJsonPath('data.rating.value', 4.9);
    }

    public function test_auto_rating_recalculates_when_testimonial_changes(): void
    {
        $service = app(ProductService::class);
        $service->resetProduct();

        $product = FeaturedProduct::first();
        $product->update(['rating_mode' => 'auto']);

        Testimonial::truncate();
        $t1 = Testimonial::create(['name' => 'A', 'location_or_role' => 'Lahore', 'rating' => 5, 'product' => 'P', 'review' => 'R', 'status' => 'published']);
        $t2 = Testimonial::create(['name' => 'B', 'location_or_role' => 'Karachi', 'rating' => 5, 'product' => 'P', 'review' => 'R', 'status' => 'published']);

        $res1 = $this->getJson('/api/v1/product');
        $res1->assertJsonPath('data.rating.count', 2);
        $this->assertEquals(5.0, $res1->json('data.rating.value'));

        // Hide one testimonial
        $t2->update(['status' => 'hidden']);

        $res2 = $this->getJson('/api/v1/product');
        $res2->assertJsonPath('data.rating.count', 1);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. ADMIN AUTHENTICATION
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_auth_is_required_for_admin_product_routes(): void
    {
        $this->getJson('/api/v1/admin/product')->assertStatus(401);
        $this->putJson('/api/v1/admin/product', [])->assertStatus(401);
        $this->postJson('/api/v1/admin/product/reset')->assertStatus(401);
        $this->getJson('/api/v1/admin/product/sizes')->assertStatus(401);
        $this->postJson('/api/v1/admin/product/sizes', [])->assertStatus(401);
        $this->getJson('/api/v1/admin/product/images')->assertStatus(401);

        // Non-admin token
        $userToken = $this->authenticateRegularUser();
        $this->withHeader('Authorization', "Bearer {$userToken}")
            ->getJson('/api/v1/admin/product')
            ->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. VALIDATION
    // ─────────────────────────────────────────────────────────────────────────

    public function test_update_product_validation_failures(): void
    {
        $token = $this->authenticateAdmin();

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/product', [
                'title_line_1' => '', // required
                'title_line_2' => str_repeat('A', 65), // max 60
                'description' => str_repeat('B', 450), // max 400
                'rating_mode' => 'invalid_mode', // must be auto|manual
                'whatsapp_message_template' => '', // required
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors([
                'title_line_1',
                'title_line_2',
                'description',
                'rating_mode',
                'whatsapp_message_template',
            ]);
    }

    public function test_unknown_template_placeholder_is_rejected(): void
    {
        $token = $this->authenticateAdmin();

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/product', [
                'title_line_1' => 'MINERAL HILL',
                'title_line_2' => 'SHILAJIT',
                'rating_mode' => 'auto',
                'whatsapp_message_template' => 'Order: {product} Size: {size} Qty: {quantity} Discount: {foo}',
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['whatsapp_message_template']);
    }

    public function test_template_missing_size_or_quantity_is_rejected(): void
    {
        $token = $this->authenticateAdmin();

        // Missing {quantity}
        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/product', [
                'title_line_1' => 'MINERAL HILL',
                'title_line_2' => 'SHILAJIT',
                'rating_mode' => 'auto',
                'whatsapp_message_template' => 'Order for {product} with size {size}',
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['whatsapp_message_template']);
    }

    public function test_compare_at_price_must_be_greater_than_price(): void
    {
        $token = $this->authenticateAdmin();

        // compare_at_price equal to price
        $res1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/sizes', [
                'label' => 'Test Size',
                'price' => 100.00,
                'compare_at_price' => 100.00,
            ]);

        $res1->assertStatus(422)
            ->assertJsonValidationErrors(['compare_at_price']);

        // compare_at_price lower than price
        $res2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/sizes', [
                'label' => 'Test Size',
                'price' => 100.00,
                'compare_at_price' => 90.00,
            ]);

        $res2->assertStatus(422)
            ->assertJsonValidationErrors(['compare_at_price']);
    }

    public function test_cannot_delete_or_disable_the_only_enabled_size(): void
    {
        $token = $this->authenticateAdmin();

        ProductSize::truncate();
        $singleSize = ProductSize::create([
            'label' => 'Sole Size',
            'price' => 50.00,
            'enabled' => true,
            'is_default' => true,
        ]);

        // Try to disable it
        $resDisable = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/product/sizes/{$singleSize->id}", [
                'label' => 'Sole Size',
                'price' => 50.00,
                'enabled' => false,
            ]);

        $resDisable->assertStatus(422)
            ->assertJsonValidationErrors(['enabled']);

        // Try to delete it
        $resDelete = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/admin/product/sizes/{$singleSize->id}");

        $resDelete->assertStatus(422)
            ->assertJsonValidationErrors(['size']);
    }

    public function test_max_8_sizes_limit_is_enforced(): void
    {
        $token = $this->authenticateAdmin();

        ProductSize::truncate();
        for ($i = 1; $i <= 8; $i++) {
            ProductSize::create([
                'label' => "Size {$i}",
                'price' => 10.00 * $i,
                'enabled' => true,
                'is_default' => $i === 1,
            ]);
        }

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/sizes', [
                'label' => 'Size 9',
                'price' => 99.00,
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['size']);
    }

    public function test_max_8_images_limit_is_enforced(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();

        ProductImage::truncate();
        for ($i = 1; $i <= 8; $i++) {
            ProductImage::create([
                'path' => "products/test_{$i}.jpg",
                'sort_order' => $i,
            ]);
        }

        $file = UploadedFile::fake()->create('test_9.jpg', 100, 'image/jpeg');

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/admin/product/images', [
                'image' => $file,
            ], ['Accept' => 'application/json']);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_product_image_rejects_svg_and_files_larger_than_5mb(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();
        ProductImage::truncate();

        // 1. Reject SVG
        $svgFile = UploadedFile::fake()->create('test.svg', 10, 'image/svg+xml');
        $svgRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/admin/product/images', [
                'image' => $svgFile,
            ], ['Accept' => 'application/json']);

        $svgRes->assertStatus(422)
            ->assertJsonValidationErrors(['image']);

        // 2. Reject > 5 MB (6000 KB)
        $largeFile = UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg');
        $largeRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/admin/product/images', [
                'image' => $largeFile,
            ], ['Accept' => 'application/json']);

        $largeRes->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. CRUD & BUSINESS LOGIC
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_can_retrieve_and_update_featured_product(): void
    {
        $token = $this->authenticateAdmin();

        $getRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/product');

        $getRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'titleLine1',
                    'titleLine2',
                    'sizes',
                    'images',
                ],
            ]);

        $updateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/product', [
                'titleLine1' => 'UPDATED TITLE 1',
                'titleLine2' => 'GOLD TITLE 2',
                'description' => 'Updated description text.',
                'ratingMode' => 'manual',
                'ratingValue' => 4.7,
                'ratingCount' => 25,
                'ctaText' => 'Order Now',
                'whatsappMessageTemplate' => 'Hello! Product: {product}, Size: {size}, Qty: {quantity}, Price: {price}',
                'showQuantitySelector' => false,
                'isActive' => true,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.titleLine1', 'UPDATED TITLE 1')
            ->assertJsonPath('data.titleLine2', 'GOLD TITLE 2')
            ->assertJsonPath('data.showQuantitySelector', false);
    }

    public function test_size_crud_reorder_and_default_switching(): void
    {
        $token = $this->authenticateAdmin();
        ProductSize::truncate();

        // 1. Create first size (automatically becomes default)
        $res1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/sizes', [
                'label' => '15g',
                'price' => 208.00,
                'compareAtPrice' => 308.00,
                'enabled' => true,
            ]);

        $res1->assertStatus(201)
            ->assertJsonPath('data.isDefault', true);

        $size1Id = $res1->json('data.id');

        // 2. Create second size with is_default = true (switches default)
        $res2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/sizes', [
                'label' => '30g',
                'price' => 360.00,
                'compareAtPrice' => 499.00,
                'enabled' => true,
                'isDefault' => true,
            ]);

        $res2->assertStatus(201)
            ->assertJsonPath('data.isDefault', true);

        $size2Id = $res2->json('data.id');

        // Verify size 1 is no longer default
        $this->assertFalse(ProductSize::find($size1Id)->is_default);

        // 3. Switch default back to size 1 via default endpoint
        $resDef = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/admin/product/sizes/{$size1Id}/default");

        $resDef->assertStatus(200)
            ->assertJsonPath('data.isDefault', true);
        $this->assertFalse(ProductSize::find($size2Id)->is_default);

        // 4. Reorder sizes
        $resReorder = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/sizes/reorder', [
                'ids' => [$size2Id, $size1Id],
            ]);

        $resReorder->assertStatus(200);
        $this->assertEquals(1, ProductSize::find($size2Id)->sort_order);
        $this->assertEquals(2, ProductSize::find($size1Id)->sort_order);

        // 5. Deleting default size (size 1) automatically moves default to remaining enabled size (size 2)
        $this->assertTrue(ProductSize::find($size1Id)->is_default);
        $resDel = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/admin/product/sizes/{$size1Id}");

        $resDel->assertStatus(200);
        $this->assertTrue(ProductSize::find($size2Id)->is_default);
    }

    public function test_disabling_default_size_moves_default_to_next_enabled_size(): void
    {
        $token = $this->authenticateAdmin();
        ProductSize::truncate();

        $sizeA = ProductSize::create(['label' => 'A', 'price' => 10, 'enabled' => true, 'is_default' => true, 'sort_order' => 1]);
        $sizeB = ProductSize::create(['label' => 'B', 'price' => 20, 'enabled' => true, 'is_default' => false, 'sort_order' => 2]);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/product/sizes/{$sizeA->id}", [
                'label' => 'A',
                'price' => 10,
                'enabled' => false,
            ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.isDefault', false);

        $this->assertTrue(ProductSize::find($sizeB->id)->is_default);
    }

    public function test_image_upload_update_reorder_and_delete(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();
        ProductImage::truncate();

        $file1 = UploadedFile::fake()->create('img1.jpg', 200, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('img2.png', 300, 'image/png');

        // Upload image 1
        $res1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/admin/product/images', [
                'image' => $file1,
                'alt_text' => 'First image',
            ], ['Accept' => 'application/json']);

        $res1->assertStatus(201)
            ->assertJsonPath('data.altText', 'First image');

        $img1Id = $res1->json('data.id');
        $img1 = ProductImage::find($img1Id);
        Storage::disk('public')->assertExists($img1->path);

        // Upload image 2
        $res2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/admin/product/images', [
                'image' => $file2,
            ], ['Accept' => 'application/json']);

        $res2->assertStatus(201);
        $img2Id = $res2->json('data.id');

        // Update alt text
        $resUp = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/product/images/{$img1Id}", [
                'altText' => 'Updated Alt Text',
            ]);

        $resUp->assertStatus(200)
            ->assertJsonPath('data.altText', 'Updated Alt Text');

        // Reorder
        $resReorder = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/images/reorder', [
                'ids' => [$img2Id, $img1Id],
            ]);

        $resReorder->assertStatus(200);
        $this->assertEquals(1, ProductImage::find($img2Id)->sort_order);
        $this->assertEquals(2, ProductImage::find($img1Id)->sort_order);

        // Delete image 1 (file deleted from storage)
        $pathToDelete = $img1->path;
        $resDel = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/admin/product/images/{$img1Id}");

        $resDel->assertStatus(200);
        $this->assertNull(ProductImage::find($img1Id));
        Storage::disk('public')->assertMissing($pathToDelete);
    }

    public function test_reset_restores_defaults_and_deletes_images(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();

        // 1. Upload custom image
        $file = UploadedFile::fake()->create('custom_prod.jpg', 200, 'image/jpeg');
        $imgRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/admin/product/images', ['image' => $file], ['Accept' => 'application/json']);
        $imgId = $imgRes->json('data.id');
        $imgPath = ProductImage::find($imgId)->path;
        Storage::disk('public')->assertExists($imgPath);

        // 2. Modify product text
        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/product', [
                'titleLine1' => 'CUSTOM TITLE',
                'titleLine2' => 'CUSTOM SUB',
                'ratingMode' => 'auto',
                'whatsappMessageTemplate' => 'Size: {size} Qty: {quantity}',
            ]);

        // 3. Reset
        $resetRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/product/reset');

        $resetRes->assertStatus(200)
            ->assertJsonPath('data.titleLine1', 'MINERAL HILL')
            ->assertJsonPath('data.titleLine2', 'SHILAJIT')
            ->assertJsonPath('data.whatsappMessageTemplate', ProductService::DEFAULT_PRODUCT['whatsapp_message_template']);

        // Images emptied and deleted from storage
        Storage::disk('public')->assertMissing($imgPath);
        $this->assertEquals(0, ProductImage::count());

        // Default 4 sizes restored with 15g as default
        $this->assertEquals(4, ProductSize::count());
        $this->assertTrue(ProductSize::where('label', '15g')->first()->is_default);
    }
}
