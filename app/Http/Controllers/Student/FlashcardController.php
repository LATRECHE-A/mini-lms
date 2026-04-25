<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student flashcard controller - organized by enrolled formations.
 * Hierarchy: index (formations) -> formation (chapters/subchapters) -> subchapter (cards) -> study
 */

namespace App\Http\Controllers\Student;

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
     * Index: enrolled formations with flashcard stats.
     */
    public function index()
    {
        $user = auth()->user();
        $stats = $this->service->getStats($user);

        $formations = $user->formations()
            ->withCount('chapters')
            ->orderBy('name')
            ->get()
            ->map(function ($f) use ($user) {
                $f->card_count = Flashcard::byUser($user->id)->personal()->forFormation($f->id)->count();
                $f->due_count = Flashcard::byUser($user->id)->personal()->forFormation($f->id)
                    ->where(fn ($q) => $q->whereNull('next_review_at')->orWhere('next_review_at', '<=', now()))->count();

                return $f;
            });

        return view('student.flashcards.index', compact('stats', 'formations'));
    }

    /**
     * Formation detail: chapters/subchapters with card counts.
     */
    public function formation(Formation $formation)
    {
        $user = auth()->user();

        // Check enrollment
        if (! $user->formations()->where('formation_id', $formation->id)->exists()) {
            abort(403, 'Vous n\'êtes pas inscrit à cette formation.');
        }

        $formation->load(['chapters.subChapters']);
        $stats = $this->service->getStats($user, $formation->id);

        $subchapterIds = $formation->chapters->flatMap(fn ($ch) => $ch->subChapters->pluck('id'));

        $cardCounts = Flashcard::byUser($user->id)->personal()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        $dueCounts = Flashcard::byUser($user->id)->personal()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->where(fn ($q) => $q->whereNull('next_review_at')->orWhere('next_review_at', '<=', now()))
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        return view('student.flashcards.formation', compact('formation', 'stats', 'cardCounts', 'dueCounts'));
    }

    /**
     * Subchapter detail: show student's cards with edit/delete + add form.
     */
    public function subchapter(SubChapter $subchapter)
    {
        $user = auth()->user();
        $subchapter->load('chapter.formation');

        // Check enrollment
        $formationId = $subchapter->chapter->formation_id;
        if (! $user->formations()->where('formation_id', $formationId)->exists()) {
            abort(403);
        }

        $cards = Flashcard::byUser($user->id)->personal()
            ->forSubChapter($subchapter->id)
            ->orderBy('created_at')
            ->get();

        $dueCount = $cards->filter(fn ($c) => ! $c->next_review_at || $c->next_review_at->isPast())->count();

        return view('student.flashcards.subchapter', compact('subchapter', 'cards', 'dueCount'));
    }

    /**
     * Study mode.
     */
    public function study(Request $request)
    {
        $formationId = $request->query('formation_id');
        $subChapterId = $request->query('sub_chapter_id');
        $dueCards = $this->service->getDueCards(auth()->user(), $formationId, $subChapterId, 20);

        if ($dueCards->isEmpty()) {
            return redirect()->route('student.flashcards.index')
                ->with('success', 'Aucune carte à réviser pour le moment !');
        }

        return view('student.flashcards.study', compact('dueCards'));
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
     * Add a personal flashcard.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
            'sub_chapter_id' => 'nullable|exists:sub_chapters,id',
        ]);

        Flashcard::create([
            'user_id' => auth()->id(),
            'sub_chapter_id' => $data['sub_chapter_id'] ?? null,
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_template' => false,
        ]);

        return back()->with('success', 'Flashcard créée.');
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }
        $flashcard->update($request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
        ]));

        return back()->with('success', 'Flashcard mise à jour.');
    }

    public function destroy(Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }
        $flashcard->delete();

        return back()->with('success', 'Flashcard supprimée.');
    }

    /**
     * Generate personal flashcards from a subchapter.
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
