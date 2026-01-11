<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SecretController extends Controller
{
    protected $secretService;

    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;
    }

    /**
     * Create a new secret.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Simple validation. 
        // We could move this to a FormRequest if it gets more complex later.
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'ttl' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $text = $request->input('text');
        $ttl = $request->input('ttl');

        // Hand off to the service layer
        $secret = $this->secretService->storeSecret($text, $ttl);

        return response()->json([
            'id' => $secret->id,
            'expires_at' => $secret->expires_at,
            'link' => url("/api/v1/secrets/{$secret->id}")
        ], 201);
    }

    /**
     * Retrieve a secret.
     *
     * Returns the decrypted text. This action permanently deletes the record (Burn on read).
     *
     * @urlParam id string required The unique ID of the secret. Example: "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
     *
     * @response 200 {
     *  "text": "My super secret password"
     * }
     * @response 404 {
     *  "error": "Secret not found or already viewed."
     * }
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $decryptedSecret = $this->secretService->retrieveSecret($id);

        if ($decryptedSecret === null) {
            return response()->json(['error' => 'Secret not found or already viewed.'], 404);
        }

        return response()->json([
            'text' => $decryptedSecret,
        ]);
    }
}
