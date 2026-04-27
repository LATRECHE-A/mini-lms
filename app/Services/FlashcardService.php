<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * FlashcardService: SM-2 algorithm, AI generation (flashcards + quizzes), template cloning.
 */

namespace App\Services;

use App\Models\Flashcard;
use App\Models\Formation;
use App\Models\SubChapter;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlashcardService
{
    /**
     * SM-2 spaced repetition review.
     */
    public function review(Flashcard $card, int $quality): Flashcard
    {
        $quality = max(0, min(5, $quality));

        $ef = $card->ease_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $ef = max(1.3, $ef);

        if ($quality < 3) {
            $interval = 1;
            $difficulty = 3;
        } else {
            $interval = match (true) {
                $card->review_count === 0 => 1,
                $card->review_count === 1 => 6,
                default => (int) round($card->interval_days * $ef),
            };
            $difficulty = $quality >= 5 ? 1 : ($quality >= 4 ? 2 : 3);
        }

        $card->update([
            'ease_factor' => $ef,
            'interval_days' => min($interval, 180),
            'difficulty' => $difficulty,
            'review_count' => $card->review_count + 1,
            'last_reviewed_at' => now(),
            'next_review_at' => now()->addDays(min($interval, 180)),
        ]);

        return $card;
    }

    public function getDueCards(User $user, ?int $formationId = null, ?int $subChapterId = null, int $limit = 20)
    {
        $query = Flashcard::dueForReview($user->id)->with('subChapter.chapter');

        if ($subChapterId) {
            $query->forSubChapter($subChapterId);
        } elseif ($formationId) {
            $query->forFormation($formationId);
        }

        return $query->limit($limit)->get();
    }

    public function getStats(User $user, ?int $formationId = null): array
    {
        $base = Flashcard::byUser($user->id)->personal();
        if ($formationId) {
            $base = $base->forFormation($formationId);
        }

        return [
            'total' => (clone $base)->count(),
            'due' => (clone $base)->where(fn ($q) => $q->whereNull('next_review_at')->orWhere('next_review_at', '<=', now()))->count(),
            'mastered' => (clone $base)->where('difficulty', 1)->where('review_count', '>=', 3)->count(),
            'learning' => (clone $base)->where('review_count', '>', 0)->where('difficulty', '>', 1)->count(),
            'new' => (clone $base)->where('review_count', 0)->count(),
        ];
    }

    /**
     * Clone template flashcards to a student on enrollment.
     */
    public function cloneTemplatesForStudent(Formation $formation, User $student): int
    {
        $templates = Flashcard::templates()
            ->whereHas('subChapter.chapter', fn ($q) => $q->where('formation_id', $formation->id))
            ->get();

        $cloned = 0;
        foreach ($templates as $tpl) {
            $exists = Flashcard::byUser($student->id)
                ->forSubChapter($tpl->sub_chapter_id)
                ->where('question', $tpl->question)
                ->exists();

            if ($exists) {
                continue;
            }

            Flashcard::create([
                'user_id' => $student->id,
                'sub_chapter_id' => $tpl->sub_chapter_id,
                'question' => $tpl->question,
                'answer' => $tpl->answer,
                'is_template' => false,
            ]);
            $cloned++;
        }

        if ($cloned > 0) {
            Log::info("Cloned {$cloned} flashcards for student {$student->id}");
        }

        return $cloned;
    }

    public function removeStudentCards(Formation $formation, User $student): int
    {
        return Flashcard::byUser($student->id)
            ->forFormation($formation->id)
            ->personal()
            ->where('review_count', 0)
            ->delete();
    }

    /**
     * Generate flashcards from a subchapter via AI.
     * Admin gets BOTH templates (for distribution) AND personal copies (for studying).
     */
    public function generateFromSubChapter(SubChapter $subChapter, User $user): array
    {
        $cards = $this->callGeminiForCards($subChapter, 'flashcard');
        if (empty($cards)) {
            return [];
        }

        $isAdmin = $user->isAdmin();
        $created = [];

        foreach ($cards as $c) {
            $q = trim($c['question'] ?? '');
            $a = trim($c['answer'] ?? '');
            if (empty($q) || empty($a)) {
                continue;
            }

            if ($isAdmin) {
                // Create template (for student distribution)
                Flashcard::create([
                    'user_id' => $user->id,
                    'sub_chapter_id' => $subChapter->id,
                    'question' => $q,
                    'answer' => $a,
                    'is_template' => true,
                ]);

                // Create personal copy (so admin can study)
                $created[] = Flashcard::create([
                    'user_id' => $user->id,
                    'sub_chapter_id' => $subChapter->id,
                    'question' => $q,
                    'answer' => $a,
                    'is_template' => false,
                ]);
            } else {
                $created[] = Flashcard::create([
                    'user_id' => $user->id,
                    'sub_chapter_id' => $subChapter->id,
                    'question' => $q,
                    'answer' => $a,
                    'is_template' => false,
                ]);
            }
        }

        Log::info('Generated '.count($created)." flashcards for sub_chapter {$subChapter->id}");

        return $created;
    }

    /**
     * Generate a quiz for a subchapter via AI (Issue #2).
     * Returns quiz data array or null.
     */
    public function generateQuizForSubChapter(SubChapter $subChapter): ?array
    {
        $content = strip_tags($subChapter->content ?? '');
        if (strlen($content) < 50) {
            return null;
        }

        $prompt = <<<P
Generate a quiz with 5-8 questions from this educational content.

Title: {$subChapter->title}
Content: {$content}

Return ONLY JSON: {"title":"Quiz: {$subChapter->title}","questions":[{"question":"...","options":["A","B","C","D"],"correct_index":0,"explanation":"..."}]}
Each question must have exactly 4 options. correct_index is 0-based.
Same language as the content. No markdown.
P;

        $result = $this->callGeminiRaw($prompt);
        if (! $result) {
            return null;
        }

        $decoded = json_decode($result, true);
        if (! is_array($decoded) || empty($decoded['questions'])) {
            return null;
        }

        return $decoded;
    }

    // Private Gemini helpers

    private function callGeminiForCards(SubChapter $subChapter, string $type): array
    {
        $content = strip_tags($subChapter->content ?? '');
        if (strlen($content) < 50) {
            return [];
        }

        $prompt = <<<P
Generate 5-8 flashcards from this educational content.

Title: {$subChapter->title}
Content: {$content}

Return ONLY a JSON array: [{"question":"...","answer":"..."}]
Questions: concise (1-2 sentences). Answers: clear (1-3 sentences).
Same language as the content. No markdown.
P;

        $result = $this->callGeminiRaw($prompt);
        if (! $result) {
            return [];
        }

        $cards = json_decode($result, true);

        return is_array($cards) ? $cards : [];
    }

    private function callGeminiRaw(string $prompt): ?string
    {
        $apiKey = trim(config('services.gemini.api_key', ''));
        if (empty($apiKey)) {
            return null;
        }

        try {
            $model = config('services.gemini.model', 'gemini-2.0-flash');
            $response = Http::timeout(30)->connectTimeout(10)
                ->withHeaders(['Content-Type' => 'application/json', 'X-goog-api-key' => $apiKey])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 4096, 'responseMimeType' => 'application/json'],
                ]);

            if (! $response->successful()) {
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text') ?? '';
            $text = preg_replace('/^```(?:json)?\s*\n?/m', '', trim($text));
            $text = preg_replace('/\n?```\s*$/m', '', $text);

            return trim($text);
        } catch (\Throwable $e) {
            Log::warning("Gemini call failed: {$e->getMessage()}");

            return null;
        }
    }
}
