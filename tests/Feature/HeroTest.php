<?php

namespace Tests\Feature;

use App\Models\HeroFeature;
use App\Models\HeroSection;
use App\Models\User;
use App\Services\HeroService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateAdmin(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);
        return $admin->createToken('admin-test-token')->plainTextToken;
    }

    public function test_public_hero_endpoint_returns_expected_structure_and_only_enabled_features(): void
    {
        $hero = HeroSection::create(HeroService::DEFAULT_HERO);

        HeroFeature::create([
            'icon' => 'leaf',
            'title' => '100%',
            'subtitle' => 'PURE SHILAJIT',
            'enabled' => true,
            'sort_order' => 1,
        ]);

        HeroFeature::create([
            'icon' => 'diamond',
            'title' => 'DISABLED',
            'subtitle' => 'FEATURE',
            'enabled' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/v1/hero');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'badgeText',
                    'titleLine1',
                    'titleLine2',
                    'description',
                    'ctaText',
                    'ctaUrl',
                    'ctaEnabled',
                    'backgroundImage',
                    'backgroundImageMobile',
                    'overlayOpacity',
                    'textAlign',
                    'isActive',
                    'features' => [
                        '*' => ['id', 'icon', 'title', 'subtitle', 'enabled', 'sortOrder'],
                    ],
                ],
            ]);

        $features = $response->json('data.features');
        $this->assertCount(1, $features);
        $this->assertEquals('100%', $features[0]['title']);
    }

    public function test_admin_auth_is_required_for_admin_hero_routes(): void
    {
        $this->getJson('/api/v1/admin/hero')->assertStatus(401);
        $this->putJson('/api/v1/admin/hero', [])->assertStatus(401);
        $this->postJson('/api/v1/admin/hero/background', [])->assertStatus(401);
        $this->deleteJson('/api/v1/admin/hero/background')->assertStatus(401);
        $this->postJson('/api/v1/admin/hero/reset')->assertStatus(401);
        $this->getJson('/api/v1/admin/hero/features')->assertStatus(401);
        $this->postJson('/api/v1/admin/hero/features', [])->assertStatus(401);
        $this->postJson('/api/v1/admin/hero/features/reorder', ['ids' => [1]])->assertStatus(401);
    }

    public function test_admin_can_retrieve_hero_with_all_features(): void
    {
        $token = $this->authenticateAdmin();

        HeroSection::create(HeroService::DEFAULT_HERO);

        HeroFeature::create([
            'icon' => 'leaf',
            'title' => 'Active',
            'subtitle' => 'Badge',
            'enabled' => true,
            'sort_order' => 1,
        ]);

        HeroFeature::create([
            'icon' => 'star',
            'title' => 'Inactive',
            'subtitle' => 'Badge',
            'enabled' => false,
            'sort_order' => 2,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/hero');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Admin sees both enabled and disabled features
        $this->assertCount(2, $response->json('data.features'));
    }

    public function test_admin_can_update_hero_text_and_settings(): void
    {
        $token = $this->authenticateAdmin();
        HeroSection::create(HeroService::DEFAULT_HERO);

        $payload = [
            'titleLine1' => 'HIMALAYAN GOLD.',
            'titleLine2' => 'PURE RESIN.',
            'badgeText' => 'GOLD GRADE AUTHENTIC',
            'description' => 'Sustainably harvested at 18,000 ft in the Karakoram range.',
            'ctaText' => 'Order Now',
            'ctaUrl' => '/products',
            'ctaEnabled' => true,
            'overlayOpacity' => 25,
            'textAlign' => 'center',
            'isActive' => true,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.titleLine1', 'HIMALAYAN GOLD.')
            ->assertJsonPath('data.titleLine2', 'PURE RESIN.')
            ->assertJsonPath('data.badgeText', 'GOLD GRADE AUTHENTIC')
            ->assertJsonPath('data.overlayOpacity', 25)
            ->assertJsonPath('data.textAlign', 'center');

        $this->assertDatabaseHas('hero_sections', [
            'title_line_1' => 'HIMALAYAN GOLD.',
            'title_line_2' => 'PURE RESIN.',
            'overlay_opacity' => 25,
            'text_align' => 'center',
        ]);
    }

    public function test_update_hero_validation_failures(): void
    {
        $token = $this->authenticateAdmin();
        HeroSection::create(HeroService::DEFAULT_HERO);

        // 1. Missing title line 1
        $res1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', [
                'titleLine1' => '',
                'titleLine2' => 'Valid',
            ]);
        $res1->assertStatus(422)->assertJsonValidationErrors(['title_line_1']);

        // 2. Title lines exceeding 60 chars
        $res2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', [
                'titleLine1' => str_repeat('A', 61),
                'titleLine2' => 'Valid',
            ]);
        $res2->assertStatus(422)->assertJsonValidationErrors(['title_line_1']);

        // 3. Description exceeding 250 chars
        $res3 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', [
                'titleLine1' => 'Valid',
                'titleLine2' => 'Valid',
                'description' => str_repeat('A', 251),
            ]);
        $res3->assertStatus(422)->assertJsonValidationErrors(['description']);

        // 4. Invalid CTA URL (not internal path /# nor http(s) URL)
        $res4 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', [
                'titleLine1' => 'Valid',
                'titleLine2' => 'Valid',
                'ctaUrl' => 'ftp://invalid-protocol.com',
            ]);
        $res4->assertStatus(422)->assertJsonValidationErrors(['cta_url']);

        // 5. Invalid text align
        $res5 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', [
                'titleLine1' => 'Valid',
                'titleLine2' => 'Valid',
                'textAlign' => 'justify',
            ]);
        $res5->assertStatus(422)->assertJsonValidationErrors(['text_align']);

        // 6. Invalid overlay opacity (> 100)
        $res6 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', [
                'titleLine1' => 'Valid',
                'titleLine2' => 'Valid',
                'overlayOpacity' => 105,
            ]);
        $res6->assertStatus(422)->assertJsonValidationErrors(['overlay_opacity']);
    }

    public function test_cta_url_validation_accepts_allowed_formats_and_rejects_invalid_urls(): void
    {
        $token = $this->authenticateAdmin();
        HeroSection::create(HeroService::DEFAULT_HERO);

        $basePayload = [
            'titleLine1' => 'Valid Title 1',
            'titleLine2' => 'Valid Title 2',
        ];

        // 1. #shop accepted
        $resAnchor = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => '#shop']));
        $resAnchor->assertStatus(200)->assertJsonPath('data.ctaUrl', '#shop');

        // 2. /shop accepted
        $resPath = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => '/shop']));
        $resPath->assertStatus(200)->assertJsonPath('data.ctaUrl', '/shop');

        // 3. https://example.com accepted
        $resUrl = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => 'https://example.com']));
        $resUrl->assertStatus(200)->assertJsonPath('data.ctaUrl', 'https://example.com');

        // 4. null accepted (nullable)
        $resNull = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => null]));
        $resNull->assertStatus(200)->assertJsonPath('data.ctaUrl', null);

        // 5. javascript:alert(1) rejected
        $resJs = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => 'javascript:alert(1)']));
        $resJs->assertStatus(422)->assertJsonValidationErrors(['cta_url']);

        // 6. "shop" (no prefix) rejected
        $resNoPrefix = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => 'shop']));
        $resNoPrefix->assertStatus(422)->assertJsonValidationErrors(['cta_url']);

        // 7. mailto rejected
        $resMailto = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => 'mailto:admin@example.com']));
        $resMailto->assertStatus(422)->assertJsonValidationErrors(['cta_url']);

        // 8. empty spaces rejected
        $resSpaces = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/hero', array_merge($basePayload, ['ctaUrl' => '   ']));
        $resSpaces->assertStatus(422)->assertJsonValidationErrors(['cta_url']);
    }

    public function test_admin_can_upload_replace_and_remove_desktop_and_mobile_background_images(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();
        HeroSection::create(HeroService::DEFAULT_HERO);

        $desktopFile = UploadedFile::fake()->create('hero_desktop.jpg', 300, 'image/jpeg');

        // 1. Upload desktop background
        $uploadDesktop = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/background', [
                'image' => $desktopFile,
                'type' => 'desktop',
            ]);

        $uploadDesktop->assertStatus(200)
            ->assertJsonPath('success', true);

        $hero = HeroSection::first();
        $this->assertNotNull($hero->background_image_path);
        Storage::disk('public')->assertExists($hero->background_image_path);
        $oldDesktopPath = $hero->background_image_path;

        // 2. Replace desktop background (old file should be deleted)
        $newDesktopFile = UploadedFile::fake()->create('hero_desktop_v2.png', 400, 'image/png');
        $replaceDesktop = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/background', [
                'image' => $newDesktopFile,
                'type' => 'desktop',
            ]);

        $replaceDesktop->assertStatus(200);
        $hero->refresh();
        $this->assertNotEquals($oldDesktopPath, $hero->background_image_path);
        Storage::disk('public')->assertMissing($oldDesktopPath);
        Storage::disk('public')->assertExists($hero->background_image_path);

        // 3. Upload mobile background
        $mobileFile = UploadedFile::fake()->create('hero_mobile.webp', 200, 'image/webp');
        $uploadMobile = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/background', [
                'image' => $mobileFile,
                'type' => 'mobile',
            ]);

        $uploadMobile->assertStatus(200);
        $hero->refresh();
        $this->assertNotNull($hero->background_image_mobile_path);
        Storage::disk('public')->assertExists($hero->background_image_mobile_path);

        // 4. Remove desktop background
        $removeDesktop = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/admin/hero/background?type=desktop');

        $removeDesktop->assertStatus(200)
            ->assertJsonPath('data.backgroundImage', null);
        $hero->refresh();
        $this->assertNull($hero->background_image_path);

        // 5. Remove mobile background
        $removeMobile = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/admin/hero/background?type=mobile');

        $removeMobile->assertStatus(200)
            ->assertJsonPath('data.backgroundImageMobile', null);
        $hero->refresh();
        $this->assertNull($hero->background_image_mobile_path);
    }

    public function test_background_image_rejects_svg_and_files_larger_than_5mb(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();
        HeroSection::create(HeroService::DEFAULT_HERO);

        // SVG should be rejected
        $svgFile = UploadedFile::fake()->create('hero.svg', 50, 'image/svg+xml');
        $resSvg = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/background', [
                'image' => $svgFile,
            ]);
        $resSvg->assertStatus(422)->assertJsonValidationErrors(['image']);

        // File larger than 5 MB (5120 KB)
        $largeFile = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg');
        $resLarge = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/background', [
                'image' => $largeFile,
            ]);
        $resLarge->assertStatus(422)->assertJsonValidationErrors(['image']);
    }

    public function test_admin_can_crud_and_reorder_hero_features_and_enforce_max_6_limit(): void
    {
        $token = $this->authenticateAdmin();

        // 1. Create feature with valid icon
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/features', [
                'icon' => 'leaf',
                'title' => 'ORGANIC',
                'subtitle' => 'CERTIFIED',
                'enabled' => true,
            ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.icon', 'leaf')
            ->assertJsonPath('data.title', 'ORGANIC');

        $featureId = $createRes->json('data.id');

        // 2. Reject invalid icon
        $invalidIconRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/features', [
                'icon' => 'rocketship',
                'title' => 'Speed',
                'subtitle' => 'Fast',
            ]);
        $invalidIconRes->assertStatus(422)->assertJsonValidationErrors(['icon']);

        // 3. Update feature
        $updateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/hero/features/{$featureId}", [
                'title' => '100% PURE',
                'enabled' => false,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('data.title', '100% PURE')
            ->assertJsonPath('data.enabled', false);

        // 4. Fill up to 6 features
        $icons = ['diamond', 'flask', 'shield', 'star', 'heart'];
        $createdIds = [$featureId];

        foreach ($icons as $idx => $icon) {
            $r = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/v1/admin/hero/features', [
                    'icon' => $icon,
                    'title' => 'Badge ' . ($idx + 2),
                    'subtitle' => 'Sub ' . ($idx + 2),
                ]);
            $r->assertStatus(201);
            $createdIds[] = $r->json('data.id');
        }

        $this->assertEquals(6, HeroFeature::count());

        // 5. 7th feature must be rejected with 422
        $seventhRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/features', [
                'icon' => 'award',
                'title' => 'Extra 7',
                'subtitle' => 'Extra',
            ]);

        $seventhRes->assertStatus(422)
            ->assertJsonValidationErrors(['feature']);

        // 6. Reorder features
        $reversedIds = array_reverse($createdIds);
        $reorderRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/features/reorder', [
                'ids' => $reversedIds,
            ]);

        $reorderRes->assertStatus(200);
        $this->assertEquals($reversedIds[0], HeroFeature::orderBy('sort_order', 'asc')->first()->id);

        // 7. Delete feature
        $deleteRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/admin/hero/features/{$featureId}");

        $deleteRes->assertStatus(200);
        $this->assertEquals(5, HeroFeature::count());
    }

    public function test_admin_can_reset_hero_section_and_features_to_defaults(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();

        $hero = HeroSection::create([
            'title_line_1' => 'CUSTOM TITLE',
            'title_line_2' => 'CUSTOM SUBTITLE',
            'background_image_path' => 'hero/custom_bg.jpg',
            'background_image_mobile_path' => 'hero/custom_mobile.jpg',
            'overlay_opacity' => 50,
        ]);

        Storage::disk('public')->put('hero/custom_bg.jpg', 'fake-image-content');
        Storage::disk('public')->put('hero/custom_mobile.jpg', 'fake-image-content');

        // Add 6 random features
        for ($i = 1; $i <= 6; $i++) {
            HeroFeature::create([
                'icon' => 'star',
                'title' => 'Custom ' . $i,
                'subtitle' => 'Sub ' . $i,
                'sort_order' => $i,
            ]);
        }

        $resetRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/hero/reset');

        $resetRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.titleLine1', 'NATURAL POWER.')
            ->assertJsonPath('data.titleLine2', 'PURE SHILAJIT.')
            ->assertJsonPath('data.backgroundImage', null)
            ->assertJsonPath('data.backgroundImageMobile', null)
            ->assertJsonPath('data.overlayOpacity', 0);

        // Confirm background files deleted
        Storage::disk('public')->assertMissing('hero/custom_bg.jpg');
        Storage::disk('public')->assertMissing('hero/custom_mobile.jpg');

        // Confirm features reset to 4 defaults
        $this->assertEquals(4, HeroFeature::count());
        $this->assertDatabaseHas('hero_features', [
            'title' => '100%',
            'subtitle' => 'PURE SHILAJIT',
            'icon' => 'leaf',
        ]);
    }
}
