<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Get semua services berhasil (Test Case Positif)
     */
    public function test_can_get_all_services(): void
    {
        $user = $this->createAuthenticatedUser();
        Service::factory()->count(3)->create();

        $response = $this->withToken($user->plain_token)
                        ->getJson('/api/services');

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }

    /**
     * Test: Get semua services tanpa autentikasi gagal (Test Case Negatif)
     */
    public function test_get_services_without_auth_fails(): void
    {
        $response = $this->getJson('/api/services');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthorized - Token tidak ditemukan'
                ]);
    }

    /**
     * Test: Create service baru berhasil (Test Case Positif)
     */
    public function test_can_create_service_successfully(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->postJson('/api/services', [
                            'name' => 'AC Repair',
                            'price' => 150000,
                        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Service berhasil ditambahkan',
                    'data' => [
                        'name' => 'AC Repair',
                        'price' => 150000,
                    ]
                ]);

        $this->assertDatabaseHas('services', [
            'name' => 'AC Repair',
            'price' => 150000,
        ]);
    }

    /**
     * Test: Create service tanpa nama gagal (Test Case Negatif)
     */
    public function test_create_service_without_name_fails(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->postJson('/api/services', [
                            'price' => 150000,
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Create service dengan price negatif gagal (Test Case Negatif)
     */
    public function test_create_service_with_negative_price_fails(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->postJson('/api/services', [
                            'name' => 'Test Service',
                            'price' => -1000,
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);
    }

    /**
     * Test: Create service dengan price bukan integer gagal (Test Case Negatif)
     */
    public function test_create_service_with_non_integer_price_fails(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->postJson('/api/services', [
                            'name' => 'Test Service',
                            'price' => 'not-a-number',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);
    }

    /**
     * Test: Get detail service berhasil (Test Case Positif)
     */
    public function test_can_get_service_detail(): void
    {
        $user = $this->createAuthenticatedUser();
        $service = Service::factory()->create([
            'name' => 'House Cleaning',
            'price' => 200000,
        ]);

        $response = $this->withToken($user->plain_token)
                        ->getJson("/api/services/{$service->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $service->id,
                    'name' => 'House Cleaning',
                    'price' => 200000,
                ]);
    }

    /**
     * Test: Get detail service yang tidak ada gagal (Test Case Negatif)
     */
    public function test_get_nonexistent_service_fails(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->getJson('/api/services/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Service tidak ditemukan'
                ]);
    }

    /**
     * Test: Update service berhasil (Test Case Positif)
     */
    public function test_can_update_service_successfully(): void
    {
        $user = $this->createAuthenticatedUser();
        $service = Service::factory()->create([
            'name' => 'Old Name',
            'price' => 100000,
        ]);

        $response = $this->withToken($user->plain_token)
                        ->putJson("/api/services/{$service->id}", [
                            'name' => 'New Name',
                            'price' => 250000,
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Service berhasil diupdate',
                    'data' => [
                        'name' => 'New Name',
                        'price' => 250000,
                    ]
                ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'New Name',
            'price' => 250000,
        ]);
    }

    /**
     * Test: Update service dengan data partial berhasil (Test Case Positif)
     */
    public function test_can_partial_update_service(): void
    {
        $user = $this->createAuthenticatedUser();
        $service = Service::factory()->create([
            'name' => 'Service Name',
            'price' => 100000,
        ]);

        $response = $this->withToken($user->plain_token)
                        ->putJson("/api/services/{$service->id}", [
                            'price' => 300000,
                        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Service Name', // nama tetap
            'price' => 300000, // price berubah
        ]);
    }

    /**
     * Test: Update service yang tidak ada gagal (Test Case Negatif)
     */
    public function test_update_nonexistent_service_fails(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->putJson('/api/services/99999', [
                            'name' => 'Test',
                            'price' => 100000,
                        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Service tidak ditemukan'
                ]);
    }

    /**
     * Test: Delete service berhasil (Test Case Positif)
     */
    public function test_can_delete_service_successfully(): void
    {
        $user = $this->createAuthenticatedUser();
        $service = Service::factory()->create();

        $response = $this->withToken($user->plain_token)
                        ->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Service berhasil dihapus'
                ]);

        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
        ]);
    }

    /**
     * Test: Delete service yang tidak ada gagal (Test Case Negatif)
     */
    public function test_delete_nonexistent_service_fails(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->withToken($user->plain_token)
                        ->deleteJson('/api/services/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Service tidak ditemukan'
                ]);
    }

    /**
     * Test: Konsistensi data - service tidak hilang setelah read (Test Case Positif)
     */
    public function test_service_data_consistency_after_read(): void
    {
        $user = $this->createAuthenticatedUser();
        $service = Service::factory()->create();

        // Read pertama
        $response1 = $this->withToken($user->plain_token)
                         ->getJson("/api/services/{$service->id}");
        
        // Read kedua
        $response2 = $this->withToken($user->plain_token)
                         ->getJson("/api/services/{$service->id}");

        // Data harus sama
        $this->assertEquals(
            $response1->json('id'),
            $response2->json('id')
        );
        $this->assertEquals(
            $response1->json('name'),
            $response2->json('name')
        );
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
