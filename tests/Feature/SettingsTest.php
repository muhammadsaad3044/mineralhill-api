<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateAdmin(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);
        return $admin->createToken('admin-test-token')->plainTextToken;
    }

    public function test_admin_can_retrieve_settings(): void
    {
        $token = $this->authenticateAdmin();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['logo', 'whatsapp', 'socialLinks', 'accountLinks'],
            ]);
    }

    public function test_admin_can_update_whatsapp_configuration(): void
    {
        $token = $this->authenticateAdmin();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/admin/settings/whatsapp', [
                'phoneNumber' => '+923009998877',
                'defaultMessage' => 'Inquiring about Shilajit purity and lab tests.',
                'enabled' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.phoneNumber', '+923009998877')
            ->assertJsonPath('data.enabled', true);
    }

    public function test_admin_can_delete_whatsapp_configuration(): void
    {
        $token = $this->authenticateAdmin();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/admin/settings/whatsapp');

        $response->assertStatus(200)
            ->assertJsonPath('data.phoneNumber', '')
            ->assertJsonPath('data.enabled', false);
    }

    public function test_admin_can_upload_and_remove_logo(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();

        $file = UploadedFile::fake()->create('logo.png', 50, 'image/png');

        // Upload logo
        $uploadRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/settings/logo', [
                'logo' => $file,
            ]);

        $uploadRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotNull(SiteSetting::first()->logo_path);

        // Remove logo
        $removeRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/admin/settings/logo');

        $removeRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.logo', null);

        $this->assertNull(SiteSetting::first()->logo_path);
    }

    public function test_admin_can_upload_valid_svg_and_malicious_svg_is_rejected(): void
    {
        Storage::fake('public');
        $token = $this->authenticateAdmin();

        // 1. Valid SVG
        $validSvg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="gold"/></svg>'
        );

        $validRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/settings/logo', ['logo' => $validSvg]);

        $validRes->assertStatus(200);

        // 2. Malicious SVG with <script> tag
        $maliciousSvg = UploadedFile::fake()->createWithContent(
            'bad.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script><circle cx="10" cy="10" r="5"/></svg>'
        );

        $badRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/settings/logo', ['logo' => $maliciousSvg]);

        // Sanitizer strips <script> safely and keeps valid vector data
        $badRes->assertStatus(200);
        $savedContent = Storage::disk('public')->get(SiteSetting::first()->logo_path);
        $this->assertStringNotContainsString('<script', $savedContent);
    }

    public function test_admin_can_reset_settings_to_default(): void
    {
        $token = $this->authenticateAdmin();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/settings/reset');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.logo', null)
            ->assertJsonPath('data.whatsapp.phoneNumber', '');
    }
}
