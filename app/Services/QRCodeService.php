<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Generate QR code image from text and save to storage
     *
     * @param string $text The text to encode in QR code
     * @param string $filename Optional filename (without extension)
     * @return string|null The URL/path to the QR code image, or null on failure
     */
    public function generateAndSave(string $text, ?string $filename = null): ?string
    {
        try {
            if (!$filename) {
                $filename = Str::uuid()->toString();
            }

            // Đảm bảo thư mục tồn tại
            $directory = 'qr-codes/students';
            Storage::disk('public')->makeDirectory($directory);

            $filePath = $directory . '/' . $filename . '.png';

            // API: https://api.qrserver.com/v1/create-qr-code/
            $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($text);
            
            $qrCodeImage = @file_get_contents($apiUrl);
            
            if ($qrCodeImage === false) {
                \Log::error('Failed to generate QR code from API: ' . $apiUrl);
                return null;
            }
            
            Storage::disk('public')->put($filePath, $qrCodeImage);

            return asset('storage/' . $filePath);
        } catch (\Exception $e) {
            \Log::error('Error generating QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete QR code image from storage
     *
     * @param string $imageUrl The URL of the QR code image
     * @return bool
     */
    public function delete(string $imageUrl): bool
    {
        try {
            $path = parse_url($imageUrl, PHP_URL_PATH);
            
            if (strpos($path, '/storage/') === 0) {
                $path = substr($path, strlen('/storage/'));
            } else {
                $path = ltrim($path, '/');
            }
            
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error('Error deleting QR code: ' . $e->getMessage());
            return false;
        }
    }
}

