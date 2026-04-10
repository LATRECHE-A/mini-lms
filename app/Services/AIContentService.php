<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service for pedagogical content generation using Gemini API.
 * Generates structured course/quiz content with AI-generated diagrams and sources.
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
        'standard' => ['min_chars' => 300,  'words' => '300-500'],
        'detailed' => ['min_chars' => 600,  'words' => '600-900'],
        'exhaustive' => ['min_chars' => 1000, 'words' => '1000-1500'],
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
        User $user,
        string $prompt,
        string $type = 'mixed',
        int $chapterCount = 3,
        string $depth = 'standard',
        array $fileUris = [],
        array $urlContexts = [],
        bool $useGrounding = false,
    ): AiGeneration {

        if (! $this->isAvailable()) {
            throw new \Exception('Service IA non configuré. Ajoutez GEMINI_API_KEY dans .env.');
        }

        $rateLimitKey = 'ai-gen:'.$user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            throw new \Exception('Trop de requêtes. Réessayez dans '.RateLimiter::availableIn($rateLimitKey).' secondes.');
        }

        $fullPrompt = $this->buildPrompt($prompt, $type, $chapterCount, $depth, count($fileUris), $urlContexts);
        $rawContent = $this->callWithModelFallback($fullPrompt, $fileUris, $useGrounding);

        // Post-generation: enforce exact chapter count as safety net
        $rawContent = $this->repairChapterCount($rawContent, $chapterCount);

        RateLimiter::hit($rateLimitKey, 60);

        $generation = AiGeneration::create([
            'user_id' => $user->id,
            'prompt' => $prompt,
            'generated_content' => $rawContent,
            'type' => $type,
            'status' => 'draft',
        ]);

        ActivityLog::log($user->id, 'ai.generated', $generation, [
            'type' => $type, 'chapters' => $chapterCount, 'depth' => $depth,
        ]);

        return $generation;
    }

    // Prompt Builder (simplified for strict compliance)

    private function buildPrompt(string $userTopic, string $type, int $n, string $depth, int $fileCount, array $urlContexts): string
    {
        $wordRange = self::DEPTH_MAP[$depth]['words'] ?? '300-500';

        // URL contexts
        $ctxBlock = '';
        foreach ($urlContexts as $ctx) {
            $ctxBlock .= "\n[CONTEXT from {$ctx['url']}]\n{$ctx['title']}\n{$ctx['content_excerpt']}\n---\n";
        }

        $filesNote = $fileCount > 0
            ? "\nThe user attached {$fileCount} file(s). Use their content in the formation.\n"
            : '';

        $quizRule = $type === 'course'
            ? 'Do NOT include any quiz.'
            : 'Each chapter MUST have a "quiz" with 5-8 questions (4 options each, correct_index 0-3, explanation).';

        // Single unified prompt — no separate system/user split
        // The key to chapter count compliance: SHORT, CLEAR, REPEATED constraint
        $prompt = <<<PROMPT
Generate an educational formation as JSON.

TOPIC: {$userTopic}
{$ctxBlock}{$filesNote}
RULES:
1. The "chapters" array must have EXACTLY {$n} elements. Not {$n}-1. Not {$n}+1. Exactly {$n}.
2. Each chapter has 2-4 subchapters.
3. Each subchapter content: {$wordRange} words, HTML formatted (<h3>,<p>,<ul>,<li>,<strong>,<em>,<code>).
4. {$quizRule}
5. Write all educational content in the same language as the TOPIC above.
6. Each subchapter must include:
   - "image_query": 4-8 English words for finding a DIAGRAM or SCHEMA (not a photo). Append "diagram" or "flowchart" or "schema".
   - "source_keywords": 2-4 English terms for documentation lookup.
   - "mermaid_diagram": a valid Mermaid.js diagram (flowchart, sequence, class, or mindmap) that illustrates the subchapter's key concept. Use simple syntax. Max 15 nodes.
   - "suggested_sources": array of 2-3 objects with {"title","url","type"} where type is "docs" or "article" or "wikipedia". URLs must be real, publicly accessible pages.

OUTPUT FORMAT — respond with ONLY this JSON structure, nothing else:
{
  "chapter_title": "Formation title",
  "chapters": [exactly {$n} chapter objects]
}

Each chapter object:
{
  "title": "Chapter title",
  "subchapters": [2-4 subchapter objects],
  "quiz": {"title":"...","questions":[5-8 question objects]}
}

Each subchapter object:
{
  "title": "...",
  "content": "<h3>...</h3><p>long HTML content...</p>",
  "image_query": "concept diagram english terms",
  "source_keywords": ["term1","term2"],
  "mermaid_diagram": "graph TD\\n    A[Start] --> B[Step]\\n    B --> C[End]",
  "suggested_sources": [{"title":"...","url":"https://...","type":"docs"}]
}

Each question object:
{
  "question": "...",
  "options": ["A","B","C","D"],
  "correct_index": 0,
  "explanation": "..."
}

CRITICAL: The "chapters" array MUST contain exactly {$n} objects. Count before responding.
PROMPT;

        return $prompt;
    }

    // Post-generation repair

    private function repairChapterCount(string $rawContent, int $target): string
    {
        $json = $this->extractJson($rawContent);
        if (! $json || ! isset($json['chapters']) || ! is_array($json['chapters'])) {
            return $rawContent;
        }

        $actual = count($json['chapters']);
        if ($actual === $target) {
            return $rawContent;
        }

        if ($actual > $target) {
            Log::warning("AI repair: got {$actual} chapters, trimming to {$target}");
            $json['chapters'] = array_slice($json['chapters'], 0, $target);

            return json_encode($json, JSON_UNESCAPED_UNICODE);
        }

        Log::warning("AI: expected {$target} chapters, got {$actual} (cannot auto-pad)");

        return $rawContent;
    }

    // JSON extraction

    private function extractJson(string $content): ?array
    {
        $content = trim($content);
        $stripped = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
        $stripped = preg_replace('/\n?```\s*$/m', '', trim($stripped));

        $decoded = json_decode($stripped, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{(?:[^{}]|(?:\{(?:[^{}]|(?:\{(?:[^{}]|(?:\{[^{}]*\}))*\}))*\}))*\}/s', $content, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    // API calls

    private function callWithModelFallback(string $prompt, array $fileUris, bool $useGrounding): string
    {
        $models = array_unique(array_merge([$this->preferredModel], self::FALLBACK_MODELS));
        $lastException = null;

        foreach ($models as $model) {
            try {
                return $this->callGeminiApi($model, $prompt, $fileUris, $useGrounding);
            } catch (\Exception $e) {
                $lastException = $e;
                Log::info("AI model '{$model}' failed: {$e->getMessage()}");
                if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'introuvable')) {
                    continue;
                }
                throw $e;
            }
        }

        throw new \Exception('Aucun modèle IA disponible. '.($lastException?->getMessage() ?? ''));
    }

    private function callGeminiApi(string $model, string $prompt, array $fileUris, bool $useGrounding): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $parts = [];
        foreach ($fileUris as $f) {
            $parts[] = ['file_data' => ['file_uri' => $f['uri'], 'mime_type' => $f['mime_type']]];
        }
        $parts[] = ['text' => $prompt];

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0.2,  // Low = strict instruction following
                'maxOutputTokens' => 40000,
                'responseMimeType' => 'application/json',
            ],
        ];

        if ($useGrounding) {
            $payload['tools'] = [['googleSearch' => new \stdClass]];
        }

        $lastException = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Content-Type' => 'application/json', 'X-goog-api-key' => $this->apiKey])
                    ->post($url, $payload);

                $data = $response->json() ?? [];

                if ($response->successful()) {
                    if (isset($data['promptFeedback']['blockReason'])) {
                        throw new \Exception("Contenu bloqué par l'IA. Reformulez votre prompt.");
                    }
                    if (empty($data['candidates'])) {
                        throw new \Exception("L'IA n'a généré aucune réponse.");
                    }

                    $candidate = $data['candidates'][0];
                    if (($candidate['finishReason'] ?? '') === 'SAFETY') {
                        throw new \Exception('Réponse bloquée par les filtres de sécurité.');
                    }

                    $text = $candidate['content']['parts'][0]['text'] ?? null;
                    if ($text && strlen(trim($text)) > 50) {
                        return trim($text);
                    }

                    throw new \Exception("Réponse vide ou trop courte de l'IA.");
                }

                $errorMsg = $data['error']['message'] ?? "HTTP {$response->status()}";

                if ($response->status() === 404) {
                    throw new \Exception("Modèle '{$model}' introuvable (404).");
                }
                if (in_array($response->status(), [401, 403])) {
                    throw new \Exception('Clé API Gemini invalide.');
                }
                if ($response->status() === 400) {
                    if (str_contains($errorMsg, 'responseMimeType') || str_contains($errorMsg, 'response_mime_type')) {
                        unset($payload['generationConfig']['responseMimeType']);

                        continue;
                    }
                    throw new \Exception("Requête invalide : {$errorMsg}");
                }
                if ($response->status() === 429 || $response->status() >= 500) {
                    $lastException = new \Exception("Erreur temporaire (HTTP {$response->status()}).");
                    if ($attempt < $this->maxRetries) {
                        usleep(3_000_000 * ($attempt + 1));

                        continue;
                    }
                    break;
                }

                throw new \Exception("Erreur Gemini : {$errorMsg}");
            } catch (ConnectionException $e) {
                $lastException = $e;
                if ($attempt < $this->maxRetries) {
                    usleep(2_000_000 * ($attempt + 1));

                    continue;
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'invalide') || str_contains($e->getMessage(), 'introuvable')
                    || str_contains($e->getMessage(), 'bloqué') || str_contains($e->getMessage(), 'sécurité')) {
                    throw $e;
                }
                $lastException = $e;
                if ($attempt < $this->maxRetries) {
                    usleep(2_000_000 * ($attempt + 1));

                    continue;
                }
            }
        }

        throw new \Exception('Service IA indisponible : '.($lastException?->getMessage() ?? 'Erreur inconnue'));
    }
}
