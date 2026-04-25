<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Flashcard service: SM-2 algorithm, AI generation, template cloning on enrollment.
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

    /**
     * Get due cards, optionally filtered by formation or subchapter.
     */
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

    /**
     * Get stats for a user, optionally per formation.
     */
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
     * Get flashcards organized by formation → chapter → subchapter.
     */
    public function getOrganizedCards(User $user, bool $templatesOnly = false): array
    {
        $query = Flashcard::byUser($user->id)->with('subChapter.chapter.formation');
        if ($templatesOnly) {
            $query->templates();
        } else {
            $query->personal();
        }

        $cards = $query->orderBy('sub_chapter_id')->get();

        $organized = [];
        foreach ($cards as $card) {
            $formation = $card->formation;
            $chapter = $card->chapter;
            $sub = $card->subChapter;

            if (! $formation || ! $chapter || ! $sub) {
                $organized['Sans formation']['Divers']['Général'][] = $card;

                continue;
            }

            $organized[$formation->name][$chapter->title][$sub->title][] = $card;
        }

        return $organized;
    }

    /**
     * Clone all template flashcards of a formation to a student.
     * Called when admin enrolls a student.
     */
    public function cloneTemplatesForStudent(Formation $formation, User $student): int
    {
        $templates = Flashcard::templates()
            ->whereHas('subChapter.chapter', fn ($q) => $q->where('formation_id', $formation->id))
            ->get();

        $cloned = 0;
        foreach ($templates as $tpl) {
            // Skip if student already has a card for this subchapter+question
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
            Log::info("Cloned {$cloned} flashcards for student {$student->id} in formation {$formation->id}");
        }

        return $cloned;
    }

    /**
     * Remove cloned flashcards when student is unenrolled.
     */
    public function removeStudentCards(Formation $formation, User $student): int
    {
        return Flashcard::byUser($student->id)
            ->forFormation($formation->id)
            ->personal()
            ->where('review_count', 0) // Only delete unreviewed (don't lose progress)
            ->delete();
    }

    /**
     * Generate flashcards from a subchapter via AI.
     * If admin: creates as templates. If student: creates as personal.
     */
    public function generateFromSubChapter(SubChapter $subChapter, User $user): array
    {
        $apiKey = trim(config('services.gemini.api_key', ''));
        if (empty($apiKey)) {
            return [];
        }

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

        try {
            $model = config('services.gemini.model', 'gemini-2.0-flash');
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json', 'X-goog-api-key' => $apiKey])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 4096, 'responseMimeType' => 'application/json'],
                ]);

            if (! $response->successful()) {
                return [];
            }

            $text = $response->json('candidates.0.content.parts.0.text') ?? '';
            $text = preg_replace('/^```(?:json)?\s*\n?/m', '', trim($text));
            $text = preg_replace('/\n?```\s*$/m', '', $text);
            $cards = json_decode(trim($text), true);

            if (! is_array($cards)) {
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

                $created[] = Flashcard::create([
                    'user_id' => $user->id,
                    'sub_chapter_id' => $subChapter->id,
                    'question' => $q,
                    'answer' => $a,
                    'is_template' => $isAdmin,
                ]);
            }

            Log::info('Generated '.count($created)." flashcards (template={$isAdmin}) for sub_chapter {$subChapter->id}");

            return $created;
        } catch (\Throwable $e) {
            Log::warning("Flashcard generation failed: {$e->getMessage()}");

            return [];
        }
    }
}
