<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Secret;
use Carbon\Carbon;

class SecretTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_secret()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'text' => 'My secret password',
            'ttl' => 3600
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'expires_at', 'link']);

        $this->assertDatabaseCount('secrets', 1);
    }

    public function test_can_retrieve_and_burn_secret()
    {
        $createResponse = $this->postJson('/api/v1/secrets', [
            'text' => 'Burn after reading',
        ]);

        $id = $createResponse->json('id');

        // First read
        $readResponse = $this->getJson("/api/v1/secrets/{$id}");

        $readResponse->assertStatus(200)
            ->assertJson(['text' => 'Burn after reading']);

        // Assert deleted from DB
        $this->assertDatabaseCount('secrets', 0);

        // Second read (should fail)
        $secondReadResponse = $this->getJson("/api/v1/secrets/{$id}");
        $secondReadResponse->assertStatus(404);
    }

    public function test_cannot_read_expired_secret()
    {
        $createResponse = $this->postJson('/api/v1/secrets', [
            'text' => 'Expired secret',
            'ttl' => 1 // 1 second TTL
        ]);

        $id = $createResponse->json('id');

        // Fast forward time
        Carbon::setTestNow(Carbon::now()->addSeconds(2));

        $readResponse = $this->getJson("/api/v1/secrets/{$id}");

        $readResponse->assertStatus(404);

        // Should be deleted
        $this->assertDatabaseCount('secrets', 0);
    }

    public function test_validation_errors()
    {
        $response = $this->postJson('/api/v1/secrets', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error' => ['text']]);
    }
}
