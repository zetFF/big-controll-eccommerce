<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Cache;

class TwoFactorService
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQRCode(User $user, string $secretKey): string
    {
        $company = config('app.name');
        return $this->google2fa->getQRCodeUrl($company, $user->email, $secretKey);
    }

    public function verify(string $secretKey, string $code): bool
    {
        return $this->google2fa->verifyKey($secretKey, $code);
    }

    public function enableTwoFactor(User $user, string $code): bool
    {
        if ($this->verify($user->two_factor_secret, $code)) {
            $user->update([
                'two_factor_enabled' => true,
                'two_factor_verified_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_verified_at' => null,
        ]);
    }
} 