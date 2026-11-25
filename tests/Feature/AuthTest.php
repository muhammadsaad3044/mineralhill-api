<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(string $email = 'admin@mineralhill.com', string $password = 'admin123'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $this->createAdmin();

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@mineralhill.com',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'user' => ['id', 'name', 'email', 'isAdmin'],
            ])
            ->assertJsonPath('user.isAdmin', true);
    }

    public function test_admin_cannot_login_with_invalid_password(): void
    {
        $this->createAdmin();

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@mineralhill.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_non_admin_cannot_login_to_admin_portal(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_unauthorized_user_cannot_access_protected_admin_routes(): void
    {
        $response = $this->getJson('/api/v1/admin/settings');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/admin/testimonials');
        $response->assertStatus(401);
    }

    public function test_admin_can_get_authenticated_profile(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'admin@mineralhill.com')
            ->assertJsonPath('user.isAdmin', true);
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
