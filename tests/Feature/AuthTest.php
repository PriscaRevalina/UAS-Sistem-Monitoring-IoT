<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Register user baru berhasil (Test Case Positif)
     */
    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email'],
                    'api_token'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /**
     * Test: Register dengan email duplikat gagal (Test Case Negatif)
     */
    public function test_register_with_duplicate_email_fails(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test: Register tanpa data required gagal (Test Case Negatif)
     */
    public function test_register_without_required_fields_fails(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
    }

    /**
     * Test: Register dengan email tidak valid gagal (Test Case Negatif)
     */
    public function test_register_with_invalid_email_fails(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test: Login berhasil (Test Case Positif)
     */
    public function test_user_can_login_successfully(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email'],
                    'api_token'
                ]);
    }

    /**
     * Test: Login dengan email tidak terdaftar gagal (Test Case Negatif)
     */
    public function test_login_with_unregistered_email_fails(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'notfound@example.com',
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Email tidak ditemukan'
                ]);
    }

    /**
     * Test: Get user yang sedang login berhasil (Test Case Positif)
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
    }

    /**
     * Test: Get profile tanpa token gagal (Test Case Negatif)
     */
    public function test_get_profile_without_token_fails(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthorized - Token tidak ditemukan'
                ]);
    }

    /**
     * Test: Logout berhasil (Test Case Positif)
     */
    public function test_user_can_logout_successfully(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Logout berhasil'
                ]);

        // Verify token dihapus dari database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'api_token' => null,
        ]);
    }

    /**
     * Helper: Create authenticated user dengan token
     */
    private function createAuthenticatedUser()
    {
        $user = User::factory()->create();
        $plainToken = \Illuminate\Support\Str::random(60);
        $user->api_token = hash('sha256', $plainToken);
        $user->save();
        $user->plain_token = $plainToken;
        
        return $user;
    }
}
