<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to find relevant sources for subchapters.
 *
 * Strategy:
 * 1. PRIMARY: Use AI-suggested sources from the generation output (validated with HEAD requests)
 * 2. FALLBACK: If AI sources < 2, supplement with keyword-based Wikipedia lookup
 *
 * The old hardcoded DOCS_MAP is removed as the primary method.
 * AI-generated sources are specific to each subchapter's actual content.
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SourceFinderService
{
    private const MAX_SOURCES = 4;

    private const MIN_AI_SOURCES = 2; // Minimum AI sources before we use fallback

    /**
     * Process sources: validate AI-suggested ones, supplement with keyword fallback if needed.
     *
     * @param  array  $aiSuggestedSources  From the AI output (already parsed/normalized)
     * @param  array  $keywords  Fallback keywords for Wikipedia lookup
     * @param  string  $title  Subchapter title for context
     * @return array<array{title: string, url: string, type: string}>
     */
    public function findSources(array $aiSuggestedSources = [], array $keywords = [], string $title = ''): array
    {
        $validated = [];
        $domains = [];

        // Step 1: Validate AI-suggested sources (quick HEAD checks)
        foreach (array_slice($aiSuggestedSources, 0, 5) as $source) {
            if (count($validated) >= self::MAX_SOURCES) {
                break;
            }

            $url = $source['url'] ?? '';
            $domain = parse_url($url, PHP_URL_HOST);

            if (empty($url) || empty($domain)) {
                continue;
            }
            if (isset($domains[$domain])) {
                continue;
            } // Deduplicate by domain

            if ($this->isUrlReachable($url)) {
                $validated[] = $source;
                $domains[$domain] = true;
                Log::info("AI source validated: {$url}");
            } else {
                Log::info("AI source rejected (unreachable): {$url}");
            }
        }

        // Step 2: If we have enough AI sources, return them
        if (count($validated) >= self::MIN_AI_SOURCES) {
            return array_slice($validated, 0, self::MAX_SOURCES);
        }

        // Step 3: Supplement with Wikipedia fallback
        $keywords = array_slice(array_filter($keywords, fn ($k) => ! empty(trim($k))), 0, 3);

        foreach ($keywords as $kw) {
            if (count($validated) >= self::MAX_SOURCES) {
                break;
            }

            $wiki = $this->lookupWikipedia($kw);
            if ($wiki) {
                $domain = parse_url($wiki['url'], PHP_URL_HOST);
                if (! isset($domains[$domain])) {
                    $validated[] = $wiki;
                    $domains[$domain] = true;
                }
            }
        }

        Log::info('Sources result: '.count($validated)." sources for '{$title}'");

        return array_slice($validated, 0, self::MAX_SOURCES);
    }

    /**
     * Quick reachability check - HEAD request with short timeout.
     */
    private function isUrlReachable(string $url): bool
    {
        try {
            $response = Http::timeout(5)->connectTimeout(3)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; MiniLMS-Bot/1.0)'])
                ->head($url);

            // Accept 2xx and 3xx (redirects are fine for documentation)
            return $response->status() < 400;
        } catch (\Throwable) {
            return false;
        }
    }

    private function lookupWikipedia(string $keyword): ?array
    {
        try {
            $encoded = rawurlencode(str_replace(' ', '_', trim($keyword)));
            $response = Http::timeout(5)->connectTimeout(3)
                ->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$encoded}");

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $url = $data['content_urls']['desktop']['page'] ?? null;
            if (! $url) {
                return null;
            }

            return ['title' => $data['title'] ?? $keyword, 'url' => $url, 'type' => 'wikipedia'];
        } catch (\Throwable) {
            return null;
        }
    }
}
