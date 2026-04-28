<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student AI controller - generate, edit, confirm.
 *
 * Note: the action that was previously named `validate()` has been renamed
 * to `confirm()` to avoid a method-signature collision with the
 * `ValidatesRequests` trait inherited from the base controller (that trait
 * exposes `validate(Request $request, array $rules, ...)` and PHP's
 * strict signature compatibility refuses an override that takes a model
 * instead of an array of rules). The route `student.ai.validate` keeps
 * its name for backward UX continuity but maps to `confirm()` and lives
 * at `/ai/{generation}/confirm`.
 *
 * Robust confirm flow:
 *   - Idempotent: if already validated, redirects to the existing
 *     formation instead of creating a duplicate.
 *   - Tracks the resulting formation via ai_generations.formation_id so
 *     future actions (delete, re-confirm) know whether a formation was
 *     produced.
 *
 * Delete behaviour:
 *   - If the generation has NOT produced a formation, deletion just
 *     removes the AI row.
 *   - If it HAS produced a formation, deletion ONLY removes the AI row;
 *     the formation remains under the student's control. The student can
 *     delete the formation separately from the formations UI.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\AIGenerateRequest;
use App\Models\AiGeneration;
use App\Services\AIContentImportService;
use App\Services\AIContentParserService;
use App\Services\AIContentService;
use App\Services\ContentSanitizer;
use App\Services\GeminiFileUploadService;
use App\Services\UrlContentExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    public function __construct(
        private AIContentService $aiService,
        private AIContentParserService $parser,
        private AIContentImportService $importer,
        private GeminiFileUploadService $fileUploader,
        private UrlContentExtractorService $urlExtractor,
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

        if ($generation->hasFormation()) {
            return redirect()
                ->route('student.formations.show', $generation->formation_id)
                ->with('info', 'Cette génération a déjà été validée. Modifiez la formation directement.');
        }

        $parsed = $this->parser->parse($generation->generated_content);

        return view('student.ai.edit', compact('generation', 'parsed'));
    }

    public function update(Request $request, AiGeneration $generation)
    {
        Gate::authorize('update', $generation);

        if ($generation->hasFormation()) {
            return redirect()->route('student.ai.show', $generation)
                ->with('error', 'Génération déjà validée — modifiez la formation.');
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

        return redirect()->route('student.ai.show', $generation)->with('success', 'Contenu mis à jour.');
    }

    /**
     * Confirm the generation -> create a personal Formation owned by the
     * student, auto-enroll them, and (if type=full) seed flashcards.
     *
     * IMPORTANT: this method is intentionally named `confirm` (not
     * `validate`) to avoid colliding with the inherited
     * ValidatesRequests::validate() trait method.
     *
     * Idempotent: a second click after the formation has been created will
     * redirect to the existing formation, not duplicate it.
     */
    public function confirm(Request $request, AiGeneration $generation)
    {
        Gate::authorize('validate', $generation);

        if ($generation->hasFormation()) {
            return redirect()
                ->route('student.formations.show', $generation->formation_id)
                ->with('info', 'Cette génération est déjà validée.');
        }

        try {
            $formation = DB::transaction(function () use ($generation) {
                $generateFlashcards = $generation->type === 'full';

                $formation = $this->importer->importForStudent(
                    $generation->generated_content,
                    auth()->user(),
                    $generateFlashcards
                );

                $generation->update([
                    'status' => 'validated',
                    'validated_at' => now(),
                    'formation_id' => $formation->id,
                ]);

                return $formation;
            });

            return redirect()->route('student.formations.show', $formation)
                ->with('success', 'Formation « '.$formation->name.' » créée.');
        } catch (\Throwable $e) {
            Log::error("Student AI confirm failed: {$e->getMessage()}", ['generation_id' => $generation->id]);

            return back()->with('error', 'Erreur lors de la validation : '.$e->getMessage());
        }
    }

    public function regenerate(AiGeneration $generation)
    {
        Gate::authorize('regenerate', $generation);

        if ($generation->hasFormation()) {
            return back()->with('error', 'Cette génération est déjà validée et ne peut plus être régénérée.');
        }

        try {
            $regenType = $generation->type === 'full' ? 'mixed' : $generation->type;
            $new = $this->aiService->generate(auth()->user(), $generation->prompt, $regenType);

            if ($generation->type === 'full') {
                $new->update(['type' => 'full']);
            }

            return redirect()->route('student.ai.show', $new)->with('success', 'Régénéré !');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(AiGeneration $generation)
    {
        Gate::authorize('delete', $generation);

        $hadFormation = $generation->hasFormation();
        $generation->delete();

        $msg = $hadFormation
            ? 'Génération supprimée. La formation reste disponible dans Mes formations.'
            : 'Génération supprimée.';

        return redirect()->route('student.ai.index')->with('success', $msg);
    }
}
