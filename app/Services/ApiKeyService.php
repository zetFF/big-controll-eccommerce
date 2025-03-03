<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKeyService
{
    public function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    public function createKey(string $name, array $permissions = [], ?Carbon $expiresAt = null): ApiKey
    {
        return ApiKey::create([
            'name' => $name,
            'key' => $this->generateKey(),
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
        ]);
    }

    public function validateKey(string $key): ?ApiKey
    {
        $apiKey = ApiKey::where('key', $key)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($apiKey) {
            $apiKey->increment('uses');
            $apiKey->touch('last_used_at');
        }

        return $apiKey;
    }

    public function revokeKey(ApiKey $apiKey): void
    {
        $apiKey->update([
            'revoked_at' => now(),
        ]);
    }
} 