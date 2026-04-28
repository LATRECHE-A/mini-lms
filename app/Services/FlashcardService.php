<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * FlashcardService - SM-2 review, stats, AI generation, template cloning,
 * and the admin-personal-copy guarantee.
 *
 * The "admin can study" guarantee:
 *   For every template owned by an admin, that admin also has a personal
 *   copy with the same question/answer in the same sub-chapter.
 *   `ensurePersonalCopiesForAdmin()` brings any admin's deck up to that
 *   invariant idempotently and is called from every entry point that
 *   could change it (study, store, update, generate, destroy).
 */

namespace App\Services;

use App\Models\Flashcard;
use App\Models\Formation;
use App\Models\SubChapter;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlashcardService
{
    /**
     * SM-2 spaced-repetition review.
     */
    public function review(Flashcard $card, int $quality): Flashcard
    {
        $quality = max(0, min(5, $quality));

        $ef = (float) $card->ease_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
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

        $interval = min($interval, 180);

        $card->update([
            'ease_factor' => $ef,
            'interval_days' => $interval,
            'difficulty' => $difficulty,
            'review_count' => $card->review_count + 1,
            'last_reviewed_at' => now(),
            'next_review_at' => now()->addDays($interval),
        ]);

        return $card;
    }

    public function getDueCards(User $user, ?int $formationId = null, ?int $subChapterId = null, int $limit = 20): Collection
    {
        // Make sure admin has personal copies before pulling due cards.
        if ($user->isAdmin()) {
            $this->ensurePersonalCopiesForAdmin($user, $formationId, $subChapterId);
        }

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
        if ($user->isAdmin()) {
            $this->ensurePersonalCopiesForAdmin($user, $formationId);
        }

        $base = Flashcard::query()->byUser($user->id)->personal();
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
     * Clone admin templates to a student on enrollment. Idempotent
     * (skips question collisions for that student in the same sub-chapter).
     */
    public function cloneTemplatesForStudent(Formation $formation, User $student): int
    {
        $templates = Flashcard::templates()
            ->whereHas('subChapter.chapter', fn ($q) => $q->where('formation_id', $formation->id))
            ->get();

        $cloned = 0;
        foreach ($templates as $tpl) {
            $exists = Flashcard::query()->byUser($student->id)
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
            Log::info("Cloned {$cloned} flashcards for student {$student->id} on formation {$formation->id}");
        }

        return $cloned;
    }

    public function removeStudentCards(Formation $formation, User $student): int
    {
        return Flashcard::query()->byUser($student->id)
            ->forFormation($formation->id)
            ->personal()
            ->where('review_count', 0)
            ->delete();
    }

    /**
     * For an admin: every template they own should have a corresponding
     * personal copy by the same admin. This makes "Study" reliable for
     * admins regardless of how the template was created.
     */
    public function ensurePersonalCopiesForAdmin(User $admin, ?int $formationId = null, ?int $subChapterId = null): int
    {
        if (! $admin->isAdmin()) {
            return 0;
        }

        $templatesQuery = Flashcard::query()
            ->byUser($admin->id)
            ->templates();

        if ($subChapterId) {
            $templatesQuery->forSubChapter($subChapterId);
        } elseif ($formationId) {
            $templatesQuery->forFormation($formationId);
        }

        $templates = $templatesQuery->get();
        if ($templates->isEmpty()) {
            return 0;
        }

        // Pre-fetch existing personal copies in one query.
        $existing = Flashcard::query()
            ->byUser($admin->id)
            ->personal()
            ->whereIn('sub_chapter_id', $templates->pluck('sub_chapter_id')->unique())
            ->get(['sub_chapter_id', 'question'])
            ->groupBy('sub_chapter_id')
            ->map(fn ($g) => $g->pluck('question')->all());

        $created = 0;
        foreach ($templates as $tpl) {
            $taken = $existing[$tpl->sub_chapter_id] ?? [];
            if (in_array($tpl->question, $taken, true)) {
                continue;
            }

            Flashcard::create([
                'user_id' => $admin->id,
                'sub_chapter_id' => $tpl->sub_chapter_id,
                'question' => $tpl->question,
                'answer' => $tpl->answer,
                'is_template' => false,
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * After an admin updates a template, mirror the new question/answer
     * onto their personal copy (matched by previous question). Keeps
     * admin's study deck in sync.
     */
    public function syncAdminPersonalAfterTemplateChange(User $admin, Flashcard $template, string $oldQuestion): void
    {
        if (! $admin->isAdmin() || ! $template->is_template) {
            return;
        }

        Flashcard::query()
            ->byUser($admin->id)
            ->personal()
            ->forSubChapter($template->sub_chapter_id)
            ->where('question', $oldQuestion)
            ->update([
                'question' => $template->question,
                'answer' => $template->answer,
            ]);
    }

    /**
     * After an admin deletes a template, also drop their matching personal
     * copy (templates and admin-personal copies live and die together).
     */
    public function removeAdminPersonalForTemplate(User $admin, Flashcard $template): int
    {
        if (! $admin->isAdmin() || ! $template->is_template) {
            return 0;
        }

        return Flashcard::query()
            ->byUser($admin->id)
            ->personal()
            ->forSubChapter($template->sub_chapter_id)
            ->where('question', $template->question)
            ->delete();
    }

    /**
     * AI-generate flashcards from a sub-chapter.
     *
     * Admin: creates BOTH a template (for distribution) and a personal copy
     * (for studying) per generated card, skipping question-duplicates.
     * Student: creates only personal copies.
     *
     * Returns the personal-copy models (the studyable ones), since callers
     * report "N flashcards generated" from a study perspective.
     */
    public function generateFromSubChapter(SubChapter $subChapter, User $user): array
    {
        $cards = $this->callGeminiForCards($subChapter);
        if (empty($cards)) {
            return [];
        }

        $isAdmin = $user->isAdmin();
        $created = [];

        // Pre-load existing question signatures for de-duplication.
        $existingPersonalQuestions = Flashcard::query()
            ->byUser($user->id)
            ->personal()
            ->forSubChapter($subChapter->id)
            ->pluck('question')
            ->all();

        $existingTemplateQuestions = $isAdmin
            ? Flashcard::query()->byUser($user->id)->templates()->forSubChapter($subChapter->id)->pluck('question')->all()
            : [];

        foreach ($cards as $c) {
            $q = trim((string) ($c['question'] ?? ''));
            $a = trim((string) ($c['answer'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }

            if ($isAdmin && ! in_array($q, $existingTemplateQuestions, true)) {
                Flashcard::create([
                    'user_id' => $user->id,
                    'sub_chapter_id' => $subChapter->id,
                    'question' => $q,
                    'answer' => $a,
                    'is_template' => true,
                ]);
                $existingTemplateQuestions[] = $q;
            }

            if (! in_array($q, $existingPersonalQuestions, true)) {
                $created[] = Flashcard::create([
                    'user_id' => $user->id,
                    'sub_chapter_id' => $subChapter->id,
                    'question' => $q,
                    'answer' => $a,
                    'is_template' => false,
                ]);
                $existingPersonalQuestions[] = $q;
            }
        }

        Log::info('Generated '.count($created)." personal flashcards for user {$user->id} on sub_chapter {$subChapter->id}");

        return $created;
    }

    /**
     * AI quiz generator (for sub-chapters).
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

    private function callGeminiForCards(SubChapter $subChapter): array
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
        $apiKey = trim((string) config('services.gemini.api_key', ''));
        if ($apiKey === '') {
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
