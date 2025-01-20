<?php

namespace app\core\src\miscellaneous;

use \app\core\src\attributes\Description;
use \app\core\src\attributes\ParameterDetails;

#[Description(
    summary: 'Default encryption handler for the application',
    author: 'RE_WEB',
    package: 'core'
)]
final class Encrypt {

    private const BITS_256 = 32;
    private const BITS_512 = 64;

    #[Description(summary: 'Generates encryption keys for use in the encryption process')]
    private static function generateKeys(): array {
        return [
            'first' => bin2hex(random_bytes(self::BITS_256)),
            'second' => bin2hex(random_bytes(self::BITS_512))
        ];
    }

    #[Description(summary: 'Fetches encryption-related configuration details')]
    private static function getConfig(): object {
        return env('encryption')->openssl;
    }

    #[Description(summary: 'Encrypts the given data using AES encryption')]
    public static function encrypt(mixed $data): string {
        $config = self::getConfig();
        $ivLength = openssl_cipher_iv_length($config->method);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $first = openssl_encrypt($data, $config->method, base64_decode($config->firstKey), OPENSSL_RAW_DATA, $iv);
        $second = hash_hmac($config->hashMacAlgo, $first, base64_decode($config->secondKey), true);

        return base64_encode($iv . $second . $first);
    }

    #[Description(summary: 'Decrypts the provided encrypted string and validates its integrity')]
    public static function decrypt(mixed $data): bool|string {
        $config = self::getConfig();

        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length($config->method);
        $iv = substr($data, 0, $ivLength);

        $second = substr($data, $ivLength, 64);
        $first  = substr($data, $ivLength + 64);

        $decryptedData = openssl_decrypt($first, $config->method, base64_decode($config->firstKey), OPENSSL_RAW_DATA, $iv);
        $userString = hash_hmac($config->hashMacAlgo, $first, base64_decode($config->secondKey), true);

        return hash_equals($second, $userString) ? $decryptedData : false;
    }
}