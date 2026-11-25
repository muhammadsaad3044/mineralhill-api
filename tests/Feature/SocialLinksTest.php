<?php

namespace Tests\Feature;

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateAdmin(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);
        return $admin->createToken('admin-test-token')->plainTextToken;
    }

    public function test_admin_can_crud_social_links(): void
    {
        $token = $this->authenticateAdmin();

        // 1. Create
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/social-links', [
                'platform' => 'Instagram',
                'label' => 'Official Instagram',
                'url' => 'https://instagram.com/mineralhill',
                'enabled' => true,
            ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('data.platform', 'Instagram');

        $id = $createRes->json('data.id');

        // 2. Update
        $updateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/social-links/{$id}", [
                'url' => 'https://instagram.com/mineralhill_updated',
                'enabled' => false,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('data.url', 'https://instagram.com/mineralhill_updated')
            ->assertJsonPath('data.enabled', false);

        // 3. Delete
        $deleteRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/admin/social-links/{$id}");

        $deleteRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('social_links', ['id' => $id]);
    }

    public function test_duplicate_platform_is_rejected_unless_custom(): void
    {
        $token = $this->authenticateAdmin();

        SocialLink::create([
            'platform' => 'Facebook',
            'url' => 'https://facebook.com/1',
            'enabled' => true,
            'sort_order' => 1,
        ]);

        // Attempting to create duplicate Facebook should fail
        $duplicateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/social-links', [
                'platform' => 'Facebook',
                'url' => 'https://facebook.com/2',
            ]);

        $duplicateRes->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);

        // Custom platform duplicate is allowed
        SocialLink::create([
            'platform' => 'Custom',
            'url' => 'https://custom1.com',
            'enabled' => true,
            'sort_order' => 2,
        ]);

        $customRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/social-links', [
                'platform' => 'Custom',
                'url' => 'https://custom2.com',
            ]);

        $customRes->assertStatus(201);
    }

    public function test_admin_can_reorder_social_links(): void
    {
        $token = $this->authenticateAdmin();

        $l1 = SocialLink::create(['platform' => 'Facebook', 'url' => 'https://fb.com', 'sort_order' => 1]);
        $l2 = SocialLink::create(['platform' => 'Twitter', 'url' => 'https://x.com', 'sort_order' => 2]);
        $l3 = SocialLink::create(['platform' => 'YouTube', 'url' => 'https://yt.com', 'sort_order' => 3]);

        $reorderRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/social-links/reorder', [
                'ids' => [$l3->id, $l1->id, $l2->id],
            ]);

        $reorderRes->assertStatus(200);

        $this->assertEquals(1, $l3->fresh()->sort_order);
        $this->assertEquals(2, $l1->fresh()->sort_order);
        $this->assertEquals(3, $l2->fresh()->sort_order);
    }
}
