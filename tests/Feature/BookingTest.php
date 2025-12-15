<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Get semua bookings berhasil (Test Case Positif)
     */
    public function test_can_get_all_bookings(): void
    {
        $user = $this->createAuthenticatedUser();
        $service = Service::factory()->create();
        
        Booking::factory()->count(3)->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withToken($user->plain_token)
                        ->getJson('/api/bookings');

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }

    /**
     * Test: Get bookings tanpa autentikasi gagal (Test Case Negatif)
     */
    public function test_get_bookings_without_auth_fails(): void
    {
        $response = $this->getJson('/api/bookings');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthorized - Token tidak ditemukan'
                ]);
    }

    /**
     * Test: Create booking baru berhasil (Test Case Positif)
     */
    public function test_can_create_booking_successfully(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->withToken($authUser->plain_token)
                        ->postJson('/api/bookings', [
                            'user_id' => $user->id,
                            'service_id' => $service->id,
                            'booking_date' => '2025-12-20',
                            'status' => 'pending',
                        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Booking berhasil dibuat',
                    'data' => [
                        'user_id' => $user->id,
                        'service_id' => $service->id,
                        'booking_date' => '2025-12-20',
                        'status' => 'pending',
                    ]
                ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2025-12-20',
        ]);
    }

    /**
     * Test: Create booking tanpa user_id gagal (Test Case Negatif)
     */
    public function test_create_booking_without_user_id_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $service = Service::factory()->create();

        $response = $this->withToken($authUser->plain_token)
                        ->postJson('/api/bookings', [
                            'service_id' => $service->id,
                            'booking_date' => '2025-12-20',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    /**
     * Test: Create booking dengan user_id yang tidak ada gagal (Test Case Negatif)
     */
    public function test_create_booking_with_nonexistent_user_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $service = Service::factory()->create();

        $response = $this->withToken($authUser->plain_token)
                        ->postJson('/api/bookings', [
                            'user_id' => 99999,
                            'service_id' => $service->id,
                            'booking_date' => '2025-12-20',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    /**
     * Test: Create booking dengan service_id yang tidak ada gagal (Test Case Negatif)
     */
    public function test_create_booking_with_nonexistent_service_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();

        $response = $this->withToken($authUser->plain_token)
                        ->postJson('/api/bookings', [
                            'user_id' => $user->id,
                            'service_id' => 99999,
                            'booking_date' => '2025-12-20',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['service_id']);
    }

    /**
     * Test: Create booking dengan format tanggal salah gagal (Test Case Negatif)
     */
    public function test_create_booking_with_invalid_date_format_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->withToken($authUser->plain_token)
                        ->postJson('/api/bookings', [
                            'user_id' => $user->id,
                            'service_id' => $service->id,
                            'booking_date' => 'not-a-date',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['booking_date']);
    }

    /**
     * Test: Create booking dengan status invalid gagal (Test Case Negatif)
     */
    public function test_create_booking_with_invalid_status_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->withToken($authUser->plain_token)
                        ->postJson('/api/bookings', [
                            'user_id' => $user->id,
                            'service_id' => $service->id,
                            'booking_date' => '2025-12-20',
                            'status' => 'invalid_status',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test: Get detail booking berhasil (Test Case Positif)
     */
    public function test_can_get_booking_detail(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2025-12-20',
            'status' => 'confirmed',
        ]);

        $response = $this->withToken($authUser->plain_token)
                        ->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $booking->id,
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'status' => 'confirmed',
                ]);
    }

    /**
     * Test: Get detail booking yang tidak ada gagal (Test Case Negatif)
     */
    public function test_get_nonexistent_booking_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();

        $response = $this->withToken($authUser->plain_token)
                        ->getJson('/api/bookings/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Booking tidak ditemukan'
                ]);
    }

    /**
     * Test: Update booking berhasil (Test Case Positif)
     */
    public function test_can_update_booking_successfully(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2025-12-20',
            'status' => 'pending',
        ]);

        $response = $this->withToken($authUser->plain_token)
                        ->putJson("/api/bookings/{$booking->id}", [
                            'booking_date' => '2025-12-25',
                            'status' => 'confirmed',
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Booking berhasil diupdate',
                    'data' => [
                        'booking_date' => '2025-12-25',
                        'status' => 'confirmed',
                    ]
                ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_date' => '2025-12-25',
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test: Update booking status dari pending ke completed berhasil (Test Case Positif)
     */
    public function test_can_update_booking_status_flow(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        // Update ke confirmed
        $this->withToken($authUser->plain_token)
            ->putJson("/api/bookings/{$booking->id}", ['status' => 'confirmed'])
            ->assertStatus(200);

        // Update ke completed
        $response = $this->withToken($authUser->plain_token)
                        ->putJson("/api/bookings/{$booking->id}", [
                            'status' => 'completed',
                        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test: Update booking yang tidak ada gagal (Test Case Negatif)
     */
    public function test_update_nonexistent_booking_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();

        $response = $this->withToken($authUser->plain_token)
                        ->putJson('/api/bookings/99999', [
                            'status' => 'confirmed',
                        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Booking tidak ditemukan'
                ]);
    }

    /**
     * Test: Delete booking berhasil (Test Case Positif)
     */
    public function test_can_delete_booking_successfully(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $service = Service::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withToken($authUser->plain_token)
                        ->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Booking berhasil dihapus'
                ]);

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }

    /**
     * Test: Delete booking yang tidak ada gagal (Test Case Negatif)
     */
    public function test_delete_nonexistent_booking_fails(): void
    {
        $authUser = $this->createAuthenticatedUser();

        $response = $this->withToken($authUser->plain_token)
                        ->deleteJson('/api/bookings/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Booking tidak ditemukan'
                ]);
    }

    /**
     * Test: Konsistensi relasi - booking memiliki user dan service (Test Case Positif)
     */
    public function test_booking_has_correct_relationships(): void
    {
        $authUser = $this->createAuthenticatedUser();
        $user = User::factory()->create(['name' => 'Test User']);
        $service = Service::factory()->create(['name' => 'Test Service']);
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withToken($authUser->plain_token)
                        ->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'user' => ['id', 'name', 'email'],
                    'service' => ['id', 'name', 'price'],
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
