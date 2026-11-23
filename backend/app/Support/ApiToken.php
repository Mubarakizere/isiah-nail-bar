<?php

namespace App\Support;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiToken
{
    private const LIFETIME_SECONDS = 28800;

    public static function issue(User $user): string
    {
        $now = time();

        return JWT::encode([
            'sub' => $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::LIFETIME_SECONDS,
        ], self::signingKey(), 'HS256');
    }

    public static function resolve(?string $token): ?User
    {
        if (!$token) {
            return null;
        }

        try {
            $payload = JWT::decode($token, new Key(self::signingKey(), 'HS256'));
            return isset($payload->sub) ? User::find($payload->sub) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function signingKey(): string
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }
}
