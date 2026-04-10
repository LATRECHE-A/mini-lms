<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to enrich subchapter data with images from Wikimedia and Pexels based on a query.
 * Tries Wikimedia first for CC/public-domain images, and falls back to Pexels if no suitable image is found. 
 * Adds image URL, alt text, and credit information.
 */

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageEnrichmentService
{
    public function __construct(
        private WikimediaImageService $wikimedia,
        private PexelsImageService $pexels,
    ) {}

    public function enrichSubchapter(array $subchapter): array
    {
        $query = $subchapter['image_query'] ?? '';
        if (empty(trim($query))) return $subchapter;

        $image = $this->wikimedia->findImage($query);

        if (!$image) {
            $image = $this->pexels->findImage($query);
        }

        if ($image) {
            $subchapter['image_url']    = $image['url'];
            $subchapter['image_alt']    = $image['alt'];
            $subchapter['image_credit'] = $image['credit'];
            Log::info("Image found for '{$query}': {$image['url']}");
        } else {
            Log::info("No image found for '{$query}'");
        }

        return $subchapter;
    }
}
