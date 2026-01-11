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
        // Happy path: User sends text, gets a link back
        $response = $this->postJson('/api/v1/secrets', [
            'text' => 'super secret',
            'ttl' => 3600
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'expires_at', 'link']);
    }

    public function test_secret_is_burned_after_read()
    {
        // 1. Create it
        $createResponse = $this->postJson('/api/v1/secrets', ['text' => 'burn me']);
        $id = $createResponse->json('id');

        // 2. Read it (should work)
        $this->getJson("/api/v1/secrets/{$id}")
            ->assertStatus(200)
            ->assertJson(['text' => 'burn me']);

        // 3. Read it again (should fail because it's gone)
        $this->getJson("/api/v1/secrets/{$id}")
            ->assertStatus(404);
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
