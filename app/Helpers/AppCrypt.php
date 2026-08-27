<?php

namespace App\Helpers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AppCrypt
{
    /**
     * Mencoba melakukan dekripsi secara aman.
     * Mengembalikan nilai asli jika sukses, atau null jika gagal/dimanipulasi.
     */
    public static function decrypt(?string $payload, array $metadata = []): mixed
    {
        // Cegah error jika payload kosong
        if (blank($payload)) {
            return null;
        }

        try {
            return Crypt::decrypt($payload);
        } catch (DecryptException $e) {
            // Log ini sebagai peringatan keamanan (Security Warning)
            AppLog::warning(
                'Terdeteksi manipulasi atau kegagalan dekripsi token',
                'decryption_failed',
                [],
                array_merge($metadata, [
                    'invalid_payload' => $payload,
                    'error_message' => $e->getMessage(),
                ]),
                'security' // Sangat disarankan membuat channel 'security.log' khusus
            );

            return null;
        }
    }
}
