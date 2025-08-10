<?php

namespace App\Utils;

use Exception;

class ObfuscationUtil
{
    /**
     * Decrypts the given encrypted data after verifying the HMAC signature.
     *
     * @param string $encryptedData
     * @param string $clientSignature
     * @return array
     * @throws Exception
     */
    public static function decryptPayload(string $encryptedData, string $clientSignature): array
    {
        // Validate HMAC signature
        $sharedSecret = env('OBFUSCATION_KEY');
        $computedSignature = hash_hmac('sha256', $encryptedData, $sharedSecret);

        if (!hash_equals($clientSignature, $computedSignature)) {
            throw new Exception('Invalid signature', 403);
        }

        // Load private key
        $privateKeyPath = storage_path('keys/private.pem');
        if (!file_exists($privateKeyPath)) {
            throw new Exception('Private key not found', 500);
        }

        $privateKey = file_get_contents($privateKeyPath);

        // Base64 decode and decrypt
        $encryptedBinary = base64_decode($encryptedData);
        if ($encryptedBinary === false) {
            throw new Exception('Base64 decoding failed', 400);
        }

        $decrypted = null;
        $success = openssl_private_decrypt(
            $encryptedBinary,
            $decrypted,
            $privateKey,
            OPENSSL_PKCS1_OAEP_PADDING
        );

        if (!$success || !$decrypted) {
            throw new Exception('Decryption failed', 400);
        }

        // Decode JSON
        $payload = json_decode($decrypted, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in decrypted payload', 400);
        }

        return $payload;
    }
}
