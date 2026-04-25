<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service for pedagogical content generation using Gemini API.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AiGeneration;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AIContentService
{
    private string $apiKey;

    private string $preferredModel;

    private int $timeout;

    private int $maxRetries;

    private const FALLBACK_MODELS = [
        'gemini-2.0-flash',
        'gemini-1.5-flash',
        'gemini-1.5-flash-latest',
        'gemini-pro',
    ];

    private const DEPTH_MAP = [
        'standard' => '300-500',
        'detailed' => '600-900',
        'exhaustive' => '1000-1500',
    ];

    public function __construct()
    {
        $this->apiKey = trim(config('services.gemini.api_key', ''));
        $this->preferredModel = config('services.gemini.model', 'gemini-2.0-flash');
        $this->timeout = (int) config('services.gemini.timeout', 90);
        $this->maxRetries = (int) config('services.gemini.max_retries', 2);
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function generate(
        User $user, string $prompt, string $type = 'mixed',
        int $chapterCount = 3, string $depth = 'standard',
        array $fileUris = [], array $urlContexts = [], bool $useGrounding = false,
    ): AiGeneration {

        if (! $this->isAvailable()) {
            throw new \Exception('Service IA non configuré. Ajoutez GEMINI_API_KEY dans .env.');
        }

        $rateLimitKey = 'ai-gen:'.$user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            throw new \Exception('Trop de requêtes. Réessayez dans '.RateLimiter::availableIn($rateLimitKey).' secondes.');
        }

        $fullPrompt = $this->buildPrompt($prompt, $type, $chapterCount, $depth, count($fileUris), $urlContexts);
        $rawContent = $this->callWithModelFallback($fullPrompt, $fileUris, $useGrounding, $chapterCount);

        $rawContent = $this->repairChapterCount($rawContent, $chapterCount);

        RateLimiter::hit($rateLimitKey, 60);

        $generation = AiGeneration::create([
            'user_id' => $user->id, 'prompt' => $prompt,
            'generated_content' => $rawContent, 'type' => $type, 'status' => 'draft',
        ]);

        ActivityLog::log($user->id, 'ai.generated', $generation, [
            'type' => $type, 'chapters' => $chapterCount, 'depth' => $depth,
        ]);

        return $generation;
    }

    /**
     * Minimal, brutally clear prompt. Every unnecessary word is removed.
     * The chapter count N is the FIRST thing stated and repeated at the end.
     */
    private function buildPrompt(string $topic, string $type, int $n, string $depth, int $fileCount, array $urlContexts): string
    {
        $words = self::DEPTH_MAP[$depth] ?? '300-500';

        $ctx = '';
        foreach ($urlContexts as $c) {
            $ctx .= "\n[Source: {$c['url']}] {$c['title']}: {$c['content_excerpt']}\n";
        }

        $files = $fileCount > 0 ? "Use the {$fileCount} attached file(s) as source material.\n" : '';

        $quiz = $type === 'course' ? '' : <<<'Q'
Each chapter needs a "quiz" object:
{"title":"Quiz title","questions":[5-8 objects with "question","options":[4 strings],"correct_index":0-3,"explanation"]}
Q;

        return <<<P
Create a formation about: {$topic}
{$ctx}{$files}
CHAPTERS: EXACTLY {$n}. Not more. Not less. Array length must be {$n}.

Per chapter: 2-4 subchapters. Per subchapter: {$words} words in HTML (h3,p,ul,li,strong,em,code).

Per subchapter also include:
- "image_query": 4-8 English words for finding a diagram/schema
- "source_keywords": 2-4 English terms
- "mermaid_diagram": valid Mermaid.js code (flowchart/sequence/class, max 15 nodes, simple syntax)
- "suggested_sources": 2-3 objects {"title","url","type":"docs|article|wikipedia"} with real URLs

{$quiz}

Same language as the topic. Return ONLY JSON:
{"chapter_title":"Title","chapters":[{$n} objects with "title","subchapters":[...],"quiz":{...}]}

Array length = {$n}. Verify before responding.
P;
    }

    private function repairChapterCount(string $raw, int $target): string
    {
        $json = $this->extractJson($raw);
        if (! $json || ! isset($json['chapters']) || ! is_array($json['chapters'])) {
            return $raw;
        }

        $actual = count($json['chapters']);
        if ($actual === $target) {
            return $raw;
        }

        if ($actual > $target) {
            Log::warning("AI: trimming {$actual} → {$target} chapters");
            $json['chapters'] = array_slice($json['chapters'], 0, $target);

            return json_encode($json, JSON_UNESCAPED_UNICODE);
        }

        Log::warning("AI: got {$actual} chapters, expected {$target}");

        return $raw;
    }

    private function extractJson(string $content): ?array
    {
        $content = trim($content);
        $s = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
        $s = preg_replace('/\n?```\s*$/m', '', trim($s));

        $d = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($d)) {
            return $d;
        }

        if (preg_match('/\{(?:[^{}]|(?:\{(?:[^{}]|(?:\{(?:[^{}]|(?:\{[^{}]*\}))*\}))*\}))*\}/s', $content, $m)) {
            $d = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($d)) {
                return $d;
            }
        }

        return null;
    }

    private function callWithModelFallback(string $prompt, array $fileUris, bool $useGrounding, int $chapterCount): string
    {
        $models = array_unique(array_merge([$this->preferredModel], self::FALLBACK_MODELS));
        $last = null;

        foreach ($models as $model) {
            try {
                return $this->callGeminiApi($model, $prompt, $fileUris, $useGrounding, $chapterCount);
            } catch (\Exception $e) {
                $last = $e;
                Log::info("Model '{$model}' failed: {$e->getMessage()}");
                if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'introuvable')) {
                    continue;
                }
                throw $e;
            }
        }

        throw new \Exception('Aucun modèle IA disponible. '.($last?->getMessage() ?? ''));
    }

    private function callGeminiApi(string $model, string $prompt, array $fileUris, bool $useGrounding, int $chapterCount): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $parts = [];
        foreach ($fileUris as $f) {
            $parts[] = ['file_data' => ['file_uri' => $f['uri'], 'mime_type' => $f['mime_type']]];
        }
        $parts[] = ['text' => $prompt];

        // Use responseSchema to enforce exact array length when model supports it
        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 40000,
                'responseMimeType' => 'application/json',
            ],
        ];

        if ($useGrounding) {
            $payload['tools'] = [['googleSearch' => new \stdClass]];
        }

        $last = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Content-Type' => 'application/json', 'X-goog-api-key' => $this->apiKey])
                    ->post($url, $payload);

                $data = $response->json() ?? [];

                if ($response->successful()) {
                    if (isset($data['promptFeedback']['blockReason'])) {
                        throw new \Exception("Contenu bloqué par l'IA.");
                    }
                    if (empty($data['candidates'])) {
                        throw new \Exception('Aucune réponse.');
                    }
                    if (($data['candidates'][0]['finishReason'] ?? '') === 'SAFETY') {
                        throw new \Exception('Filtres de sécurité.');
                    }

                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text && strlen(trim($text)) > 50) {
                        return trim($text);
                    }
                    throw new \Exception('Réponse vide.');
                }

                $err = $data['error']['message'] ?? "HTTP {$response->status()}";
                if ($response->status() === 404) {
                    throw new \Exception("Modèle '{$model}' introuvable (404).");
                }
                if (in_array($response->status(), [401, 403])) {
                    throw new \Exception('Clé API invalide.');
                }
                if ($response->status() === 400) {
                    if (str_contains($err, 'responseMimeType') || str_contains($err, 'response_mime_type')) {
                        unset($payload['generationConfig']['responseMimeType']);

                        continue;
                    }
                    throw new \Exception("Requête invalide : {$err}");
                }
                if ($response->status() === 429 || $response->status() >= 500) {
                    $last = new \Exception("HTTP {$response->status()}");
                    if ($attempt < $this->maxRetries) {
                        usleep(3_000_000 * ($attempt + 1));

                        continue;
                    }
                    break;
                }
                throw new \Exception("Erreur Gemini : {$err}");
            } catch (ConnectionException $e) {
                $last = $e;
                if ($attempt < $this->maxRetries) {
                    usleep(2_000_000 * ($attempt + 1));

                    continue;
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'invalide') || str_contains($e->getMessage(), 'introuvable')
                    || str_contains($e->getMessage(), 'bloqué') || str_contains($e->getMessage(), 'sécurité')) {
                    throw $e;
                }
                $last = $e;
                if ($attempt < $this->maxRetries) {
                    usleep(2_000_000 * ($attempt + 1));

                    continue;
                }
            }
        }

        throw new \Exception('Service IA indisponible : '.($last?->getMessage() ?? ''));
    }
}
