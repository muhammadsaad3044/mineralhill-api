<?php

namespace Tests\Feature;

use App\Models\AccountLink;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_settings_endpoint_returns_expected_structure(): void
    {
        SiteSetting::create([
            'id' => 1,
            'whatsapp_number' => '+923001234567',
            'whatsapp_message' => 'Hello Mineral Hill',
            'whatsapp_enabled' => true,
        ]);

        SocialLink::create([
            'platform' => 'Instagram',
            'url' => 'https://instagram.com/mineralhill',
            'enabled' => true,
            'sort_order' => 1,
        ]);

        AccountLink::create([
            'type' => 'Email',
            'label' => 'Support',
            'value' => 'support@mineralhill.com',
            'enabled' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'logo',
                    'whatsapp' => ['enabled', 'phoneNumber', 'defaultMessage'],
                    'socialLinks',
                    'accountLinks',
                ],
            ])
            ->assertJsonPath('data.whatsapp.phoneNumber', '+923001234567');
    }

    public function test_public_testimonials_endpoint_returns_only_published_records(): void
    {
        Testimonial::create([
            'name' => 'Published User',
            'location_or_role' => 'Lahore',
            'rating' => 5,
            'product' => 'Resin 50g',
            'review' => 'This is a genuine published review of at least 20 chars.',
            'status' => 'published',
            'featured' => false,
        ]);

        Testimonial::create([
            'name' => 'Pending User',
            'location_or_role' => 'Karachi',
            'rating' => 4,
            'product' => 'Resin 30g',
            'review' => 'This is a pending review waiting for approval from admin.',
            'status' => 'pending',
            'featured' => false,
        ]);

        Testimonial::create([
            'name' => 'Hidden User',
            'location_or_role' => 'Islamabad',
            'rating' => 2,
            'product' => 'Resin 15g',
            'review' => 'This is a hidden review that should not appear publicly.',
            'status' => 'hidden',
            'featured' => false,
        ]);

        $response = $this->getJson('/api/v1/testimonials');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Published User');
    }

    public function test_public_testimonials_can_filter_featured_only(): void
    {
        Testimonial::create([
            'name' => 'Normal Published',
            'location_or_role' => 'Peshawar',
            'rating' => 5,
            'product' => 'Resin 50g',
            'review' => 'A great regular published testimonial that is not featured.',
            'status' => 'published',
            'featured' => false,
        ]);

        Testimonial::create([
            'name' => 'Featured Published',
            'location_or_role' => 'Skardu',
            'rating' => 5,
            'product' => 'Resin 100g',
            'review' => 'An exceptional featured testimonial pinned to the landing page.',
            'status' => 'published',
            'featured' => true,
        ]);

        $response = $this->getJson('/api/v1/testimonials?featured=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Featured Published');
    }
}
