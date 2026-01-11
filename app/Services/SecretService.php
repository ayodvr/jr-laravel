<?php

namespace App\Services;

use App\Repositories\SecretRepositoryInterface;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class SecretService
{
    protected $secretRepository;

    public function __construct(SecretRepositoryInterface $secretRepository)
    {
        $this->secretRepository = $secretRepository;
    }

    public function storeSecret(string $text, ?int $ttl = null)
    {
        // Calculate expiration if TTL is provided
        // Defaults to null (never expires) if not set
        $expiresAt = $ttl ? Carbon::now()->addSeconds($ttl) : null;
        
        // We encrypt the content BEFORE it hits the DB.
        // Even if the DB is leaked, the secrets are useless without the app key.
        $encryptedContent = Crypt::encryptString($text);

        return $this->secretRepository->create([
            'content' => $encryptedContent,
            'expires_at' => $expiresAt,
        ]);
    }

    public function retrieveSecret(string $id): ?string
    {
        $secret = $this->secretRepository->find($id);

        if (!$secret) {
            return null;
        }

        // Check if the secret has expired
        if ($secret->expires_at && Carbon::now()->greaterThan($secret->expires_at)) {
            // It's expired, so we treat it as if it doesn't exist
            // Also clean it up to save space
            $this->secretRepository->delete($id);
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($secret->content);
        } catch (\Exception $e) {
            // If decryption fails, something is wrong with the key or data
            // Just return null for safety
            return null;
        }

        // The core "Burn on Read" feature:
        // We delete the secret immediately after retrieving it.
        $this->secretRepository->delete($id);

        return $decrypted;
    }
}
