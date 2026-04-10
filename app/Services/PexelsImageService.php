<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to fetch landscape images from Pexels based on a query, with error handling and logging.
 * Uses Pexels API to search for images, returns image URL, alt text, and credit information.
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PexelsImageService
{
    public function findImage(string $query): ?array
    {
        $apiKey = config('services.pexels.api_key', '');
        if (empty($apiKey) || empty(trim($query))) return null;

        try {
            $response = Http::timeout(8)->connectTimeout(5)
                ->withHeaders(['Authorization' => $apiKey])
                ->get('https://api.pexels.com/v1/search', [
                    'query'       => $query,
                    'per_page'    => 5,
                    'orientation' => 'landscape',
                ]);

            if (!$response->successful()) return null;

            $photos = $response->json('photos') ?? [];
            if (empty($photos)) return null;

            $photo = $photos[0];
            $url   = $photo['src']['large'] ?? $photo['src']['medium'] ?? null;
            if (!$url) return null;

            return [
                'url'    => $url,
                'alt'    => mb_substr($photo['alt'] ?? $query, 0, 500),
                'credit' => 'Photo by ' . ($photo['photographer'] ?? 'Unknown') . ' on Pexels',
            ];
        } catch (\Throwable $e) {
            Log::warning("Pexels error for '{$query}': {$e->getMessage()}");
            return null;
        }
    }
}
