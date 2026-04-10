<?php

/*
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to find CC/public-domain images from Wikimedia Commons based on a search query.
 * Uses Wikimedia API to search for images, validates licenses, and returns image URL, alt text, and credit information.
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikimediaImageService
{
    public function findImage(string $query): ?array
    {
        if (empty(trim($query))) return null;

        try {
            $response = Http::timeout(8)->connectTimeout(5)->get(
                'https://commons.wikimedia.org/w/api.php', [
                    'action'       => 'query',
                    'generator'    => 'search',
                    'gsrnamespace' => 6,
                    'gsrsearch'    => $query,
                    'gsrlimit'     => 5,
                    'prop'         => 'imageinfo',
                    'iiprop'       => 'url|extmetadata|mime',
                    'iiurlwidth'   => 800,
                    'format'       => 'json',
                ]
            );

            if (!$response->successful()) {
                Log::info("Wikimedia API returned {$response->status()} for '{$query}'");
                return null;
            }

            $pages = $response->json('query.pages') ?? [];

            foreach ($pages as $page) {
                $info = $page['imageinfo'][0] ?? null;
                if (!$info) continue;

                $mime = $info['mime'] ?? '';
                if (!str_starts_with($mime, 'image/')) continue;

                $meta    = $info['extmetadata'] ?? [];
                $license = $meta['LicenseShortName']['value'] ?? '';
                if ($license && !preg_match('/cc|public.?domain|pd/i', $license)) continue;

                $url = $info['thumburl'] ?? $info['url'] ?? null;
                if (!$url) continue;

                // Validate reachability
                try {
                    $head = Http::timeout(5)->connectTimeout(3)->head($url);
                    if (!str_starts_with($head->header('Content-Type') ?? '', 'image/')) continue;
                } catch (\Throwable) { continue; }

                $alt    = strip_tags($meta['ImageDescription']['value'] ?? $query);
                $artist = strip_tags($meta['Artist']['value'] ?? 'Wikimedia Commons');

                return [
                    'url'    => $url,
                    'alt'    => mb_substr($alt, 0, 500),
                    'credit' => mb_substr("{$artist} — {$license}", 0, 500),
                ];
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning("WikimediaImage error for '{$query}': {$e->getMessage()}");
            return null;
        }
    }
}
