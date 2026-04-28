<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Admin AI controller - generate, edit, import (with created_by + tracking),
 * AI rewrite, and per-sub-chapter quiz generation.
 *
 * Import is idempotent: a second click on Import returns to the existing
 * formation instead of creating a duplicate. This matches the student's
 * validate flow.
 */

namespace App\Http\Controllers\Admin;

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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $generations = AiGeneration::adminOwned()->with('user')->orderByDesc('created_at')->paginate(15);
        $studentValidated = AiGeneration::studentOwned()->validated()->with('user')->orderByDesc('validated_at')->get();
        $isAvailable = $this->aiService->isAvailable();

        return view('admin.ai.index', compact('generations', 'studentValidated', 'isAvailable'));
    }

    public function create()
    {
        $isAvailable = $this->aiService->isAvailable();

        return view('admin.ai.create', compact('isAvailable'));
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

            return redirect()->route('admin.ai.show', $generation)->with('success', 'Contenu généré avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } finally {
            $this->fileUploader->deleteAll($uploadedFiles);
        }
    }

    public function show(AiGeneration $generation)
    {
        Gate::authorize('view', $generation);
        $generation->load('user');
        $parsed = $this->parser->parse($generation->generated_content);

        return view('admin.ai.show', compact('generation', 'parsed'));
    }

    public function edit(AiGeneration $generation)
    {
        Gate::authorize('update', $generation);

        if ($generation->hasFormation()) {
            return redirect()
                ->route('admin.formations.show', $generation->formation_id)
                ->with('info', 'Cette génération a déjà été importée. Modifiez la formation directement.');
        }

        $parsed = $this->parser->parse($generation->generated_content);

        return view('admin.ai.edit', compact('generation', 'parsed'));
    }

    public function update(Request $request, AiGeneration $generation)
    {
        Gate::authorize('update', $generation);

        if ($generation->hasFormation()) {
            return redirect()->route('admin.ai.show', $generation)
                ->with('error', 'Génération déjà importée — modifiez la formation.');
        }

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
            $structure['subchapters'][] = [
                'title' => strip_tags($sub['title']),
                'content' => ContentSanitizer::render($sub['content']),
            ];
        }
        if (! empty($v['quiz_title']) && ! empty($v['questions'])) {
            $qs = [];
            foreach ($v['questions'] as $q) {
                $opts = array_map('strip_tags', $q['options']);
                $qs[] = [
                    'question' => strip_tags($q['question']),
                    'options' => $opts,
                    'correct_index' => max(0, min((int) $q['correct_index'], count($opts) - 1)),
                ];
            }
            $structure['quiz'] = ['title' => strip_tags($v['quiz_title']), 'questions' => $qs];
        }
        $generation->update(['generated_content' => $this->parser->toJson($structure)]);

        return redirect()->route('admin.ai.show', $generation)->with('success', 'Contenu mis à jour.');
    }

    public function import(Request $request, AiGeneration $generation)
    {
        Gate::authorize('import', $generation);

        // Already imported -> redirect, do NOT re-import.
        if ($generation->hasFormation()) {
            return redirect()
                ->route('admin.formations.show', $generation->formation_id)
                ->with('info', 'Cette génération est déjà importée.');
        }

        $request->validate([
            'formation_name' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'in:débutant,intermédiaire,avancé'],
            'status' => ['required', 'in:draft,published'],
        ]);

        try {
            $formation = DB::transaction(function () use ($generation, $request) {
                $generateFlashcards = $generation->type === 'full';

                $formation = $this->importer->importAsFormation(
                    $generation->generated_content,
                    auth()->user(),
                    $request->formation_name ?: null,
                    $request->level,
                    $request->status,
                    $generateFlashcards,
                );

                $generation->update([
                    'status' => 'published',
                    'formation_id' => $formation->id,
                ]);

                return $formation;
            });

            return redirect()->route('admin.formations.show', $formation)
                ->with('success', 'Formation « '.$formation->name.' » créée.');
        } catch (\Throwable $e) {
            Log::error("Admin AI import failed: {$e->getMessage()}", ['generation_id' => $generation->id]);

            return back()->with('error', 'Erreur lors de l\'import : '.$e->getMessage());
        }
    }

    public function regenerate(AiGeneration $generation)
    {
        Gate::authorize('regenerate', $generation);

        if ($generation->hasFormation()) {
            return back()->with('error', 'Cette génération est déjà importée et ne peut plus être régénérée.');
        }

        try {
            $regenType = $generation->type === 'full' ? 'mixed' : $generation->type;
            $new = $this->aiService->generate(auth()->user(), $generation->prompt, $regenType);

            if ($generation->type === 'full') {
                $new->update(['type' => 'full']);
            }

            return redirect()->route('admin.ai.show', $new)->with('success', 'Régénéré.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete the AI generation row only. The Formation it produced (if
     * any) is preserved — the admin can delete it separately from the
     * formations UI. Same principle as the student flow.
     */
    public function destroy(AiGeneration $generation)
    {
        Gate::authorize('delete', $generation);

        $hadFormation = $generation->hasFormation();
        $generation->delete();

        $msg = $hadFormation
            ? 'Génération supprimée. La formation reste disponible.'
            : 'Génération supprimée.';

        return redirect()->route('admin.ai.index')->with('success', $msg);
    }

    /** AI partial rewrite — AJAX. */
    public function rewrite(Request $request)
    {
        $data = $request->validate([
            'selected_text' => ['required', 'string', 'min:5', 'max:10000'],
            'instruction' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $apiKey = trim((string) config('services.gemini.api_key', ''));
        if ($apiKey === '') {
            return response()->json(['error' => 'Service IA non configuré.'], 422);
        }

        $prompt = "You are an educational content editor. Rewrite the following text according to the instruction.\n\n"
            ."Instruction: {$data['instruction']}\n\nText to rewrite:\n{$data['selected_text']}\n\n"
            .'RULES: Return ONLY the rewritten text. Same language. Keep HTML formatting if present. No markdown.';

        try {
            $model = config('services.gemini.model', 'gemini-2.0-flash');
            $response = Http::timeout(30)->connectTimeout(10)
                ->withHeaders(['Content-Type' => 'application/json', 'X-goog-api-key' => $apiKey])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 4096],
                ]);

            if (! $response->successful()) {
                return response()->json(['error' => 'Erreur Gemini : '.($response->json('error.message') ?? $response->status())], 500);
            }

            $text = trim($response->json('candidates.0.content.parts.0.text') ?? '');
            $text = preg_replace('/^```(?:html)?\s*\n?/m', '', $text);
            $text = preg_replace('/\n?```\s*$/m', '', $text);

            if (trim($text) === '') {
                return response()->json(['error' => 'Réponse vide.'], 500);
            }

            return response()->json(['rewritten' => trim($text)]);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Impossible de contacter le service IA.'], 500);
        } catch (\Throwable $e) {
            Log::warning("AI rewrite error: {$e->getMessage()}");

            return response()->json(['error' => 'Erreur : '.$e->getMessage()], 500);
        }
    }

    public function generateQuiz(SubChapter $subchapter)
    {
        if ($subchapter->quiz) {
            return back()->with('error', 'Ce sous-chapitre a déjà un quiz. Supprimez-le d\'abord.');
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
