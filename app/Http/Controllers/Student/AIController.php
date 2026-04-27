<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student AI Controller - generate, edit, validate (with ownership), quiz generation.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\AIGenerateRequest;
use App\Models\AiGeneration;
use App\Models\SubChapter;
use App\Services\AIContentImportService;
use App\Services\AIContentParserService;
use App\Services\AIContentService;
use App\Services\ContentSanitizer;
use App\Services\FlashcardService;
use App\Services\GeminiFileUploadService;
use App\Services\UrlContentExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AIController extends Controller
{
    public function __construct(
        private AIContentService $aiService,
        private AIContentParserService $parser,
        private AIContentImportService $importer,
        private GeminiFileUploadService $fileUploader,
        private UrlContentExtractorService $urlExtractor,
        private FlashcardService $flashcardService,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $drafts = AiGeneration::byUser($user->id)->draft()->orderByDesc('created_at')->get();
        $validated = AiGeneration::byUser($user->id)->validated()->orderByDesc('validated_at')->get();
        $isAvailable = $this->aiService->isAvailable();

        return view('student.ai.index', compact('drafts', 'validated', 'isAvailable'));
    }

    public function create()
    {
        $isAvailable = $this->aiService->isAvailable();

        return view('student.ai.create', compact('isAvailable'));
    }

    public function generate(AIGenerateRequest $request)
    {
        $uploadedFiles = [];
        try {
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $uploadedFiles[] = $this->fileUploader->uploadFile($file);
                }
            }
            $fileUris = array_map(fn ($f) => ['uri' => $f['uri'], 'mime_type' => $f['mime_type']], $uploadedFiles);
            $urlResult = $this->urlExtractor->extractFromPrompt($request->prompt);
            $useGrounding = ! empty($urlResult['failed_urls']) && empty($urlResult['contexts']);

            $aiType = $request->type === 'full' ? 'mixed' : $request->type;

            $generation = $this->aiService->generate(
                user: $request->user(),
                prompt: $request->prompt,
                type: $aiType,
                chapterCount: (int) $request->chapter_count,
                depth: $request->depth,
                fileUris: $fileUris,
                urlContexts: $urlResult['contexts'],
                useGrounding: $useGrounding,
            );

            if ($request->type === 'full') {
                $generation->update(['type' => 'full']);
            }

            return redirect()->route('student.ai.show', $generation)->with('success', 'Contenu généré !');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } finally {
            $this->fileUploader->deleteAll($uploadedFiles);
        }
    }

    public function show(AiGeneration $generation)
    {
        Gate::authorize('view', $generation);
        $parsed = $this->parser->parse($generation->generated_content);

        return view('student.ai.show', compact('generation', 'parsed'));
    }

    public function edit(AiGeneration $generation)
    {
        Gate::authorize('update', $generation);
        $parsed = $this->parser->parse($generation->generated_content);

        return view('student.ai.edit', compact('generation', 'parsed'));
    }

    public function update(Request $request, AiGeneration $generation)
    {
        Gate::authorize('update', $generation);
        $v = $request->validate([
            'chapter_title' => ['required', 'string', 'max:255'],
            'subchapters' => ['nullable', 'array'],
            'subchapters.*.title' => ['required', 'string', 'max:255'],
            'subchapters.*.content' => ['required', 'string', 'max:65000'],
            'quiz_title' => ['nullable', 'string', 'max:255'],
            'questions' => ['nullable', 'array'],
            'questions.*.question' => ['required', 'string', 'max:1000'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.options.*' => ['required', 'string', 'max:500'],
            'questions.*.correct_index' => ['required', 'integer', 'min:0'],
        ]);

        $structure = ['chapter_title' => strip_tags($v['chapter_title']), 'subchapters' => [], 'quiz' => null];
        foreach (($v['subchapters'] ?? []) as $sub) {
            $structure['subchapters'][] = ['title' => strip_tags($sub['title']), 'content' => ContentSanitizer::render($sub['content'])];
        }
        if (! empty($v['quiz_title']) && ! empty($v['questions'])) {
            $qs = [];
            foreach ($v['questions'] as $q) {
                $opts = array_map('strip_tags', $q['options']);
                $qs[] = ['question' => strip_tags($q['question']), 'options' => $opts, 'correct_index' => max(0, min((int) $q['correct_index'], count($opts) - 1))];
            }
            $structure['quiz'] = ['title' => strip_tags($v['quiz_title']), 'questions' => $qs];
        }
        $generation->update(['generated_content' => $this->parser->toJson($structure)]);

        return redirect()->route('student.ai.show', $generation)->with('success', 'Contenu mis à jour.');
    }

    public function validate(Request $request, AiGeneration $generation)
    {
        Gate::authorize('validate', $generation);
        try {
            $formation = DB::transaction(function () use ($generation) {
                $generateFlashcards = $generation->type === 'full';
                $formation = $this->importer->importForStudent($generation->generated_content, auth()->user(), $generateFlashcards);
                $generation->update(['status' => 'validated', 'validated_at' => now()]);

                return $formation;
            });

            return redirect()->route('student.formations.show', $formation)->with('success', 'Formation « '.$formation->name.' » créée.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function regenerate(AiGeneration $generation)
    {
        Gate::authorize('regenerate', $generation);
        try {
            $new = $this->aiService->generate(auth()->user(), $generation->prompt, $generation->type);

            return redirect()->route('student.ai.show', $new)->with('success', 'Régénéré !');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(AiGeneration $generation)
    {
        Gate::authorize('delete', $generation);
        $generation->delete();

        return redirect()->route('student.ai.index')->with('success', 'Supprimé.');
    }

    /**
     * Generate quiz for a subchapter via AI.
     */
    public function generateQuiz(SubChapter $subchapter)
    {
        // Check enrollment
        $formation = $subchapter->chapter?->formation;
        if (! $formation || ! $formation->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        if ($subchapter->quiz) {
            return back()->with('error', 'Ce sous-chapitre a déjà un quiz.');
        }

        $quizData = $this->flashcardService->generateQuizForSubChapter($subchapter);

        if (! $quizData || empty($quizData['questions'])) {
            return back()->with('error', 'Impossible de générer le quiz. Réessayez.');
        }

        $quiz = $subchapter->quiz()->create([
            'title' => $quizData['title'] ?? "Quiz : {$subchapter->title}",
            'status' => 'published',
        ]);

        foreach ($quizData['questions'] as $i => $q) {
            $question = $quiz->questions()->create([
                'question_text' => strip_tags($q['question'] ?? ''),
                'order' => $i + 1,
            ]);
            foreach (($q['options'] ?? []) as $j => $opt) {
                $question->answers()->create([
                    'answer_text' => strip_tags($opt),
                    'is_correct' => $j === (int) ($q['correct_index'] ?? 0),
                ]);
            }
        }

        return back()->with('success', 'Quiz généré avec '.count($quizData['questions']).' questions.');
    }
}
