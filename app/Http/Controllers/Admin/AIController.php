<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Admin AI Controller - generation, editing, import, AI partial rewrite.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AIGenerateRequest;
use App\Models\AiGeneration;
use App\Services\AIContentImportService;
use App\Services\AIContentParserService;
use App\Services\AIContentService;
use App\Services\ContentSanitizer;
use App\Services\GeminiFileUploadService;
use App\Services\UrlContentExtractorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
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

            $generation = $this->aiService->generate(
                user: $request->user(),
                prompt: $request->prompt,
                type: $request->type,
                chapterCount: (int) $request->chapter_count,
                depth: $request->depth,
                fileUris: $fileUris,
                urlContexts: $urlResult['contexts'],
                useGrounding: $useGrounding,
            );

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
        $parsed = $this->parser->parse($generation->generated_content);

        return view('admin.ai.edit', compact('generation', 'parsed'));
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

        return redirect()->route('admin.ai.show', $generation)->with('success', 'Contenu mis à jour.');
    }

    public function import(Request $request, AiGeneration $generation)
    {
        Gate::authorize('import', $generation);
        $request->validate([
            'formation_name' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'in:débutant,intermédiaire,avancé'],
            'status' => ['required', 'in:draft,published'],
        ]);
        try {
            $formation = $this->importer->importAsFormation($generation->generated_content, $request->formation_name ?: null, $request->level, $request->status);
            $generation->update(['status' => 'published']);

            return redirect()->route('admin.formations.show', $formation)->with('success', 'Formation « '.$formation->name.' » créée.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function regenerate(AiGeneration $generation)
    {
        Gate::authorize('regenerate', $generation);
        try {
            $new = $this->aiService->generate(auth()->user(), $generation->prompt, $generation->type);

            return redirect()->route('admin.ai.show', $new)->with('success', 'Régénéré.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(AiGeneration $generation)
    {
        Gate::authorize('delete', $generation);
        $generation->delete();

        return redirect()->route('admin.ai.index')->with('success', 'Supprimé.');
    }

    /**
     * AI partial rewrite - AJAX endpoint.
     * Receives selected text + instruction, returns rewritten text via Gemini.
     */
    public function rewrite(Request $request)
    {
        $data = $request->validate([
            'selected_text' => ['required', 'string', 'min:5', 'max:10000'],
            'instruction' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $apiKey = trim(config('services.gemini.api_key', ''));
        if (empty($apiKey)) {
            return response()->json(['error' => 'Service IA non configuré. Ajoutez GEMINI_API_KEY dans .env.'], 422);
        }

        $prompt = "You are an educational content editor. Rewrite the following text according to the instruction.\n\n"
            ."Instruction: {$data['instruction']}\n\n"
            ."Text to rewrite:\n{$data['selected_text']}\n\n"
            ."RULES:\n"
            ."- Return ONLY the rewritten text.\n"
            ."- Keep the same language as the original.\n"
            ."- Keep HTML formatting if present (p, h3, ul, li, strong, em, code).\n"
            .'- No markdown fences. No explanations. Just the rewritten text.';

        $model = config('services.gemini.model', 'gemini-2.0-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            $response = Http::timeout(30)->connectTimeout(10)
                ->withHeaders(['Content-Type' => 'application/json', 'X-goog-api-key' => $apiKey])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 4096],
                ]);

            if (! $response->successful()) {
                $errMsg = $response->json('error.message') ?? "HTTP {$response->status()}";
                Log::warning("AI rewrite failed: {$errMsg}");

                return response()->json(['error' => "Erreur Gemini : {$errMsg}"], 500);
            }

            $candidates = $response->json('candidates') ?? [];
            if (empty($candidates)) {
                return response()->json(['error' => "L'IA n'a généré aucune réponse."], 500);
            }

            $text = $candidates[0]['content']['parts'][0]['text'] ?? '';
            $text = trim($text);

            // Strip markdown fences if AI added them
            $text = preg_replace('/^```(?:html)?\s*\n?/m', '', $text);
            $text = preg_replace('/\n?```\s*$/m', '', $text);
            $text = trim($text);

            if (empty($text)) {
                return response()->json(['error' => 'Réponse vide de l\'IA.'], 500);
            }

            return response()->json(['rewritten' => $text]);

        } catch (ConnectionException $e) {
            Log::warning("AI rewrite connection error: {$e->getMessage()}");

            return response()->json(['error' => 'Impossible de contacter le service IA. Vérifiez votre connexion.'], 500);
        } catch (\Throwable $e) {
            Log::warning("AI rewrite error: {$e->getMessage()}");

            return response()->json(['error' => 'Erreur inattendue : '.$e->getMessage()], 500);
        }
    }
}
