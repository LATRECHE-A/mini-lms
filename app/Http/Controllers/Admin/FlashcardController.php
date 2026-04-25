<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Admin flashcard controller - manages template flashcards organized by formation.
 * Templates are auto-cloned to students on enrollment.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Models\Formation;
use App\Models\SubChapter;
use App\Services\FlashcardService;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function __construct(private FlashcardService $service) {}

    /**
     * Index: list formations with flashcard counts.
     */
    public function index()
    {
        $user = auth()->user();
        $stats = $this->service->getStats($user);

        // Formations with template flashcard counts
        $formations = Formation::withCount(['chapters'])
            ->orderBy('name')
            ->get()
            ->map(function ($f) {
                $f->template_count = Flashcard::templates()->forFormation($f->id)->count();

                return $f;
            });

        return view('admin.flashcards.index', compact('stats', 'formations'));
    }

    /**
     * Show flashcards for a specific formation, organized by chapter > subchapter.
     */
    public function formation(Formation $formation)
    {
        $formation->load(['chapters.subChapters']);

        $user = auth()->user();
        $stats = $this->service->getStats($user, $formation->id);

        // Get template counts per subchapter
        $subchapterIds = $formation->chapters->flatMap(fn ($ch) => $ch->subChapters->pluck('id'));

        $templateCounts = Flashcard::templates()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        // Admin's own study cards counts
        $personalCounts = Flashcard::byUser($user->id)->personal()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        return view('admin.flashcards.formation', compact('formation', 'stats', 'templateCounts', 'personalCounts'));
    }

    /**
     * Show all cards for a subchapter (templates + admin's personal).
     */
    public function subchapter(SubChapter $subchapter)
    {
        $subchapter->load('chapter.formation');

        $templates = Flashcard::templates()->forSubChapter($subchapter->id)->orderBy('created_at')->get();
        $personal = Flashcard::byUser(auth()->id())->personal()->forSubChapter($subchapter->id)->orderBy('created_at')->get();

        return view('admin.flashcards.subchapter', compact('subchapter', 'templates', 'personal'));
    }

    /**
     * Study mode - admin's own cards.
     */
    public function study(Request $request)
    {
        $formationId = $request->query('formation_id');
        $subChapterId = $request->query('sub_chapter_id');
        $dueCards = $this->service->getDueCards(auth()->user(), $formationId, $subChapterId, 20);

        if ($dueCards->isEmpty()) {
            return redirect()->route('admin.flashcards.index')
                ->with('success', 'Aucune carte à réviser !');
        }

        return view('admin.flashcards.study', compact('dueCards'));
    }

    /**
     * Review (AJAX).
     */
    public function review(Request $request, Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }
        $request->validate(['quality' => 'required|integer|min:0|max:5']);
        $this->service->review($flashcard, (int) $request->quality);

        return response()->json(['ok' => true]);
    }

    /**
     * Store manually - admin can create template or personal.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
            'sub_chapter_id' => 'required|exists:sub_chapters,id',
            'is_template' => 'nullable|boolean',
        ]);

        Flashcard::create([
            'user_id' => auth()->id(),
            'sub_chapter_id' => $data['sub_chapter_id'],
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_template' => $data['is_template'] ?? true,
        ]);

        return back()->with('success', 'Flashcard créée.');
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        $data = $request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
        ]);
        $flashcard->update($data);

        return back()->with('success', 'Flashcard mise à jour.');
    }

    public function destroy(Flashcard $flashcard)
    {
        $flashcard->delete();

        return back()->with('success', 'Flashcard supprimée.');
    }

    /**
     * Generate via AI - creates templates.
     */
    public function generate(SubChapter $subchapter)
    {
        $created = $this->service->generateFromSubChapter($subchapter, auth()->user());

        return back()->with(
            empty($created) ? 'error' : 'success',
            empty($created) ? 'Impossible de générer. Réessayez.' : count($created).' flashcards générées.'
        );
    }
}
