<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialsTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateAdmin(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);
        return $admin->createToken('admin-test-token')->plainTextToken;
    }

    public function test_admin_can_list_testimonials_with_stats_and_filters(): void
    {
        $token = $this->authenticateAdmin();

        Testimonial::create([
            'name' => 'Dr. Tariq',
            'location_or_role' => 'Lahore',
            'rating' => 5,
            'product' => 'Gold Grade 50g',
            'review' => 'Incredible purity and authentic quality that boosted my morning stamina.',
            'status' => 'published',
            'featured' => true,
        ]);

        Testimonial::create([
            'name' => 'Sarah',
            'location_or_role' => 'Islamabad',
            'rating' => 4,
            'product' => 'Pure Resin 30g',
            'review' => 'Very effective supplement for my workouts and sustained mental clarity.',
            'status' => 'pending',
            'featured' => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/testimonials');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'testimonials',
                'stats' => ['total', 'published', 'pending', 'hidden', 'averageRating', 'distribution'],
                'pagination',
            ])
            ->assertJsonPath('stats.total', 2)
            ->assertJsonPath('stats.published', 1)
            ->assertJsonPath('stats.pending', 1);
    }

    public function test_admin_can_create_update_and_delete_testimonial_with_avatar(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();

        $avatarFile = UploadedFile::fake()->create('avatar.jpg', 50, 'image/jpeg');

        // 1. Create with multipart
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/testimonials', [
                'name' => 'Hamza Bilal',
                'role' => 'Marathon Runner, Karachi',
                'avatar' => $avatarFile,
                'rating' => 5,
                'product' => 'Pure Shilajit Jumbo Pack (100g)',
                'review' => 'Purest Himalayan resin I have tested. Clean dissolve with no bitter aftertaste.',
                'status' => 'published',
                'featured' => true,
            ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Hamza Bilal')
            ->assertJsonPath('data.rating', 5);

        $id = $createRes->json('data.id');
        $this->assertDatabaseHas('testimonials', ['id' => $id, 'featured' => true]);

        // 2. Update
        $updateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/testimonials/{$id}", [
                'rating' => 4,
                'featured' => false,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.featured', false);

        // 3. Delete
        $deleteRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/admin/testimonials/{$id}");

        $deleteRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('testimonials', ['id' => $id]);
    }

    public function test_admin_can_bulk_update_status(): void
    {
        $token = $this->authenticateAdmin();

        $t1 = Testimonial::create([
            'name' => 'User 1',
            'location_or_role' => 'City 1',
            'rating' => 5,
            'product' => 'Jar 15g',
            'review' => 'Great product with remarkable stamina boost across daily activities.',
            'status' => 'pending',
        ]);

        $t2 = Testimonial::create([
            'name' => 'User 2',
            'location_or_role' => 'City 2',
            'rating' => 4,
            'product' => 'Jar 30g',
            'review' => 'High quality resin with fast shipping and authentic lab documentation.',
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/testimonials/bulk-status', [
                'ids' => [$t1->id, $t2->id],
                'status' => 'published',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('updatedCount', 2);

        $this->assertEquals('published', $t1->fresh()->status);
        $this->assertEquals('published', $t2->fresh()->status);
    }

    public function test_admin_can_bulk_delete_testimonials(): void
    {
        $token = $this->authenticateAdmin();

        $t1 = Testimonial::create([
            'name' => 'Delete Me 1',
            'location_or_role' => 'City 1',
            'rating' => 1,
            'product' => 'Jar 15g',
            'review' => 'Test review for deletion testing that meets character requirement.',
            'status' => 'hidden',
        ]);

        $t2 = Testimonial::create([
            'name' => 'Delete Me 2',
            'location_or_role' => 'City 2',
            'rating' => 1,
            'product' => 'Jar 30g',
            'review' => 'Another test review for deletion testing meeting length rule.',
            'status' => 'hidden',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/testimonials/bulk-delete', [
                'ids' => [$t1->id, $t2->id],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('deletedCount', 2);

        $this->assertDatabaseMissing('testimonials', ['id' => $t1->id]);
        $this->assertDatabaseMissing('testimonials', ['id' => $t2->id]);
    }
}
