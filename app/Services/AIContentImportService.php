<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to import AI-generated content as Formations.
 * Handles enrichment, quiz creation, and flashcard generation.
 * Sets created_by for ownership tracking.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\SubChapter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIContentImportService
{
    public function __construct(
        private AIContentParserService $parser,
        private ImageEnrichmentService $imageEnrichment,
        private SourceFinderService $sourceFinder,
        private FlashcardService $flashcardService,
    ) {}

    /**
     * Admin import - sets created_by to the admin.
     */
    public function importAsFormation(
        string $rawContent, User $user, ?string $formationName = null,
        string $level = 'débutant', string $status = 'draft', bool $generateFlashcards = false,
    ): Formation {
        $parsed = $this->parser->parse($rawContent);
        if (! $parsed['parsed']) {
            throw new \Exception("Le contenu n'a pas pu être analysé. Régénérez le contenu.");
        }

        return $this->createFormationFromParsed($parsed, $user, $formationName, $level, $status, $generateFlashcards);
    }

    /**
     * Student validation - sets created_by to the student, auto-enrolls.
     */
    public function importForStudent(string $rawContent, User $student, bool $generateFlashcards = false): Formation
    {
        $parsed = $this->parser->parse($rawContent);
        if (! $parsed['parsed']) {
            throw new \Exception('Contenu non analysable. Régénérez avant de valider.');
        }

        return DB::transaction(function () use ($parsed, $student, $generateFlashcards) {
            $formation = $this->createFormationFromParsed($parsed, $student, null, 'débutant', 'published', $generateFlashcards);
            $formation->update(['description' => 'Contenu personnel généré par IA — '.$student->name]);
            $formation->students()->attach($student->id, ['enrolled_at' => now()]);
            ActivityLog::log($student->id, 'formation.imported_from_ai', $formation);

            return $formation;
        });
    }

    private function createFormationFromParsed(
        array $parsed, User $creator, ?string $formationName,
        string $level, string $status, bool $generateFlashcards,
    ): Formation {
        return DB::transaction(function () use ($parsed, $creator, $formationName, $level, $status, $generateFlashcards) {

            $formation = Formation::create([
                'name' => $formationName ?: $parsed['chapter_title'],
                'description' => 'Formation générée par IA.',
                'level' => $level,
                'status' => $status,
                'created_by' => $creator->id,
            ]);

            $chapters = $parsed['chapters'] ?? [];

            if (empty($chapters) && ! empty($parsed['subchapters'])) {
                $chapters = [[
                    'title' => $parsed['chapter_title'] ?? 'Chapitre 1',
                    'subchapters' => $parsed['subchapters'],
                    'quiz' => $parsed['quiz'] ?? null,
                ]];
            }

            Log::info("Importing formation '{$formation->name}' with ".count($chapters)." chapters (by user {$creator->id})");

            $allSubChapters = [];

            foreach ($chapters as $ci => $chapterData) {
                $chapter = $this->createChapter($formation, $chapterData, $ci);

                $subs = $chapterData['subchapters'] ?? [];
                $subCount = count($subs);
                $quizData = $chapterData['quiz'] ?? null;

                foreach ($subs as $si => $subData) {
                    $subChapter = $this->createSubChapter($chapter, $subData, $si, $ci);
                    $allSubChapters[] = $subChapter;

                    if ($quizData && $si === $subCount - 1) {
                        $this->createQuiz($subChapter, $quizData);
                    }
                }

                if (empty($subs) && $quizData) {
                    $sub = SubChapter::create([
                        'chapter_id' => $chapter->id,
                        'title' => $quizData['title'] ?? 'Quiz',
                        'content' => '<p>Répondez au quiz ci-dessous.</p>',
                        'order' => 1,
                    ]);
                    $this->createQuiz($sub, $quizData);
                    $allSubChapters[] = $sub;
                }
            }

            // Generate flashcards if requested (type=full)
            if ($generateFlashcards && ! empty($allSubChapters)) {
                foreach ($allSubChapters as $sub) {
                    try {
                        $this->flashcardService->generateFromSubChapter($sub, $creator);
                    } catch (\Throwable $e) {
                        Log::warning("Flashcard generation failed for sub {$sub->id}: {$e->getMessage()}");
                    }
                }
            }

            Log::info("Formation '{$formation->name}' imported (ID: {$formation->id})");

            return $formation;
        });
    }

    private function createChapter(Formation $formation, array $data, int $index): Chapter
    {
        $chapterImage = ['image_url' => null, 'image_alt' => null, 'image_credit' => null];

        try {
            $enriched = $this->imageEnrichment->enrichSubchapter(['image_query' => ($data['title'] ?? '').' overview diagram']);
            $chapterImage = [
                'image_url' => $enriched['image_url'] ?? null,
                'image_alt' => $enriched['image_alt'] ?? null,
                'image_credit' => $enriched['image_credit'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning("Chapter {$index} image failed: {$e->getMessage()}");
        }

        $chapterSources = [];
        try {
            $allAiSources = [];
            $allKeywords = [];
            foreach (($data['subchapters'] ?? []) as $sub) {
                foreach (($sub['suggested_sources'] ?? []) as $src) {
                    $allAiSources[] = $src;
                }
                foreach (($sub['source_keywords'] ?? []) as $kw) {
                    $allKeywords[] = $kw;
                }
            }
            $seen = [];
            $unique = [];
            foreach ($allAiSources as $src) {
                $u = $src['url'] ?? '';
                if ($u && ! isset($seen[$u])) {
                    $unique[] = $src;
                    $seen[$u] = true;
                }
            }
            $chapterSources = $this->sourceFinder->findSources(
                array_slice($unique, 0, 4),
                array_unique(array_slice($allKeywords, 0, 4)),
                $data['title'] ?? ''
            );
        } catch (\Throwable $e) {
            Log::warning("Chapter {$index} sources failed: {$e->getMessage()}");
        }

        return Chapter::create([
            'formation_id' => $formation->id,
            'title' => $data['title'] ?? 'Chapitre '.($index + 1),
            'order' => $index + 1,
            'image_url' => $chapterImage['image_url'],
            'image_alt' => $chapterImage['image_alt'],
            'image_credit' => $chapterImage['image_credit'],
            'sources' => ! empty($chapterSources) ? $chapterSources : null,
        ]);
    }

    private function createSubChapter(Chapter $chapter, array $data, int $subIndex, int $chapterIndex): SubChapter
    {
        $imgData = ['image_url' => null, 'image_alt' => null, 'image_credit' => null];
        try {
            $enriched = $this->imageEnrichment->enrichSubchapter($data);
            $imgData = [
                'image_url' => $enriched['image_url'] ?? null,
                'image_alt' => $enriched['image_alt'] ?? null,
                'image_credit' => $enriched['image_credit'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning("SubChapter ch{$chapterIndex}/sub{$subIndex} image failed: {$e->getMessage()}");
        }

        $sources = [];
        try {
            $sources = $this->sourceFinder->findSources(
                $data['suggested_sources'] ?? [],
                $data['source_keywords'] ?? [],
                $data['title'] ?? ''
            );
        } catch (\Throwable $e) {
            Log::warning("SubChapter ch{$chapterIndex}/sub{$subIndex} sources failed: {$e->getMessage()}");
        }

        $mermaidCode = $data['mermaid_diagram'] ?? null;
        if (empty(trim($mermaidCode ?? ''))) {
            $mermaidCode = null;
        }

        return SubChapter::create([
            'chapter_id' => $chapter->id,
            'title' => $data['title'] ?? 'Sous-chapitre',
            'content' => ContentSanitizer::render($data['content'] ?? ''),
            'order' => $subIndex + 1,
            'image_url' => $imgData['image_url'],
            'image_alt' => $imgData['image_alt'],
            'image_credit' => $imgData['image_credit'],
            'sources' => ! empty($sources) ? $sources : null,
            'mermaid_code' => $mermaidCode,
        ]);
    }

    private function createQuiz(SubChapter $subChapter, array $quizData): void
    {
        $quiz = $subChapter->quiz()->create([
            'title' => $quizData['title'] ?? 'Quiz',
            'status' => 'published',
        ]);

        foreach (($quizData['questions'] ?? []) as $i => $qData) {
            $question = $quiz->questions()->create([
                'question_text' => strip_tags($qData['question'] ?? ''),
                'order' => $i + 1,
            ]);

            foreach (($qData['options'] ?? []) as $j => $optionText) {
                $question->answers()->create([
                    'answer_text' => strip_tags($optionText),
                    'is_correct' => $j === (int) ($qData['correct_index'] ?? 0),
                ]);
            }
        }
    }
}
