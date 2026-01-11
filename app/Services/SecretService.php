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
        // TTL in seconds
        $expiresAt = $ttl ? Carbon::now()->addSeconds($ttl) : null;
        
        // Encrypt content
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

        // Check expiration
        if ($secret->expires_at && Carbon::now()->greaterThan($secret->expires_at)) {
            $this->secretRepository->delete($id); // Clean up expired secret
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($secret->content);
        } catch (\Exception $e) {
            // Handle decryption error if any
            return null;
        }

        // Burn on read
        $this->secretRepository->delete($id);

        return $decrypted;
    }
}
