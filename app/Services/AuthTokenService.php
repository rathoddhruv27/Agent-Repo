<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Controllers\Api\AuthController;

class AuthTokenService
{
    public function generateRefreshToken(string $tokenId, int $expiresAt): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'jti' => $tokenId,
            'iat' => now()->timestamp,
            'exp' => $expiresAt,
        ];  

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->jwtSecret(),
            true
        );
        
        $signatureEncoded = $this->base64UrlEncode($signature);
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public function verifyRefreshToken(string $token): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        
        $expectedSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->jwtSecret(),
            true
        );

        $expectedSignatureEncoded = $this->base64UrlEncode($expectedSignature);
        return hash_equals($expectedSignatureEncoded, $signatureEncoded);
    }

    public function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;

        if ($remainder) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/'));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function jwtSecret(): string
    {
        $appKey = config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            return base64_decode(substr($appKey, 7));
        }

        return $appKey;
    }
}