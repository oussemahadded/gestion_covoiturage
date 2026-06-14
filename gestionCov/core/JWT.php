<?php
/**
 * core/JWT.php
 * Utilitaire pour encoder et décoder des JSON Web Tokens (HS256)
 */

class JWT
{
    // Clé secrète (en production, elle devrait venir d'une variable d'environnement ou config/app.php)
    private static string $secret = 'SESAMERIDE_SECURE_JWT_SECRET_KEY_2026_SUPER_SAFE';

    public static function encode(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Expiration par défaut : 24 heures si non spécifiée
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + (24 * 60 * 60); 
        }

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode(string $jwt): ?array
    {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $tokenParts;

        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::$secret, true);
        $base64UrlValidSignature = self::base64UrlEncode($validSignature);

        if (!hash_equals($base64UrlValidSignature, $signature)) {
            return null; // Signature invalide
        }

        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);

        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return null; // Token expiré
        }

        return $decodedPayload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
