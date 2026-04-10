<?php

/*
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to extract readable content from URLs found in user prompts.
 * Uses andreskrey/readability for content extraction, with custom logic to handle edge cases.
 * Validates URLs, blocks internal/private IPs, and logs failures without throwing exceptions.
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UrlContentExtractorService
{
    private const MAX_URLS          = 6;
    private const CONTENT_MAX_CHARS = 6000;
    private const MIN_CONTENT_CHARS = 100;

    private const BLOCKED_PATTERNS = [
        '/^localhost/i', '/^127\./', '/^10\./', '/^192\.168\./',
        '/^172\.(1[6-9]|2\d|3[01])\./', '/\.local$/i',
    ];

    public function extractFromPrompt(string $prompt): array
    {
        $urls = $this->findUrls($prompt);
        $urls = array_slice($urls, 0, self::MAX_URLS);

        if (empty($urls)) {
            return ['clean_prompt' => $prompt, 'contexts' => [], 'failed_urls' => []];
        }

        $contexts   = [];
        $failedUrls = [];

        foreach ($urls as $url) {
            $result = $this->extractUrl($url);
            if ($result) $contexts[] = $result;
            else $failedUrls[] = $url;
        }

        return ['clean_prompt' => $prompt, 'contexts' => $contexts, 'failed_urls' => $failedUrls];
    }

    public function extractUrl(string $url): ?array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) return null;

        $parsed = parse_url($url);
        if (!in_array(strtolower($parsed['scheme'] ?? ''), ['http', 'https'])) return null;

        $host = $parsed['host'] ?? '';
        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (preg_match($pattern, $host)) return null;
        }

        try {
            $response = Http::timeout(8)->connectTimeout(5)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; MiniLMS-Bot/1.0)', 'Accept' => 'text/html'])
                ->get($url);

            if (!$response->successful()) return null;

            $html = $response->body();
            $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
            $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

            $title   = 'Sans titre';
            $content = '';

            // Try Readability if available
            if (class_exists(\andreskrey\Readability\Readability::class)) {
                $config = new \andreskrey\Readability\Configuration();
                $config->setFixRelativeURLs(true);
                $config->setOriginalURL($url);
                $readability = new \andreskrey\Readability\Readability($config);
                $readability->parse($html);
                $title   = $readability->getTitle() ?: 'Sans titre';
                $content = strip_tags($readability->getContent() ?: '');
            } else {
                // Fallback: basic extraction
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                    $title = html_entity_decode(trim($m[1]));
                }
                $content = strip_tags($html);
            }

            $content = preg_replace('/\s+/', ' ', trim($content));

            if (strlen($content) < self::MIN_CONTENT_CHARS) return null;

            if (strlen($content) > self::CONTENT_MAX_CHARS) {
                $truncated = substr($content, 0, self::CONTENT_MAX_CHARS);
                $lastDot   = strrpos($truncated, '. ');
                $content   = $lastDot && $lastDot > self::CONTENT_MAX_CHARS * 0.5
                    ? substr($truncated, 0, $lastDot + 1)
                    : $truncated . '…';
            }

            return ['url' => $url, 'title' => mb_substr($title, 0, 200), 'content_excerpt' => $content];
        } catch (\Throwable $e) {
            Log::warning("URL extraction failed ({$url}): {$e->getMessage()}");
            return null;
        }
    }

    private function findUrls(string $text): array
    {
        preg_match_all('#https?://[^\s<>\[\]()\'",;]+#i', $text, $matches);
        return array_unique($matches[0] ?? []);
    }
}
