<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student flashcards.
 *
 * A student only ever sees/edits their own personal flashcards. Templates
 * authored by admins are never exposed in the student UI — they appear as
 * cloned personal cards on enrollment.
 *
 * Authorization is enforced by FlashcardPolicy + per-formation enrollment
 * checks. Generation requires the student to be enrolled in the parent
 * formation.
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

    public function index()
    {
        $user = auth()->user();
        $stats = $this->service->getStats($user);

        $formations = $user->formations()
            ->withCount('chapters')
            ->orderBy('name')
            ->get()
            ->map(function ($f) use ($user) {
                $f->card_count = Flashcard::query()->byUser($user->id)->personal()->forFormation($f->id)->count();
                $f->due_count = Flashcard::query()->byUser($user->id)->personal()->forFormation($f->id)
                    ->where(fn ($q) => $q->whereNull('next_review_at')->orWhere('next_review_at', '<=', now()))
                    ->count();

                return $f;
            });

        return view('student.flashcards.index', compact('stats', 'formations'));
    }

    public function formation(Formation $formation)
    {
        $user = auth()->user();
        $this->ensureEnrolled($user, $formation);

        $formation->load(['chapters.subChapters']);
        $stats = $this->service->getStats($user, $formation->id);

        $subchapterIds = $formation->chapters->flatMap(fn ($ch) => $ch->subChapters->pluck('id'));

        $cardCounts = Flashcard::query()->byUser($user->id)->personal()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        $dueCounts = Flashcard::query()->byUser($user->id)->personal()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->where(fn ($q) => $q->whereNull('next_review_at')->orWhere('next_review_at', '<=', now()))
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        return view('student.flashcards.formation', compact('formation', 'stats', 'cardCounts', 'dueCounts'));
    }

    public function subchapter(SubChapter $subchapter)
    {
        $user = auth()->user();
        $subchapter->load('chapter.formation');

        $formation = $subchapter->chapter?->formation;
        if (! $formation) {
            abort(404);
        }
        $this->ensureEnrolled($user, $formation);

        $cards = Flashcard::query()->byUser($user->id)->personal()
            ->forSubChapter($subchapter->id)
            ->orderBy('created_at')
            ->get();

        $dueCount = $cards->filter(fn ($c) => ! $c->next_review_at || $c->next_review_at->isPast())->count();

        return view('student.flashcards.subchapter', compact('subchapter', 'cards', 'dueCount'));
    }

    public function study(Request $request)
    {
        $formationId = $request->query('formation_id') ? (int) $request->query('formation_id') : null;
        $subChapterId = $request->query('sub_chapter_id') ? (int) $request->query('sub_chapter_id') : null;

        $user = auth()->user();

        if ($formationId) {
            $formation = Formation::findOrFail($formationId);
            $this->ensureEnrolled($user, $formation);
        }
        if ($subChapterId) {
            $subchapter = SubChapter::with('chapter.formation')->findOrFail($subChapterId);
            $formation = $subchapter->chapter?->formation;
            if (! $formation) {
                abort(404);
            }
            $this->ensureEnrolled($user, $formation);
        }

        $dueCards = $this->service->getDueCards($user, $formationId, $subChapterId, 20);

        if ($dueCards->isEmpty()) {
            $back = $subChapterId
                ? route('student.flashcards.subchapter', $subChapterId)
                : ($formationId ? route('student.flashcards.formation', $formationId) : route('student.flashcards.index'));

            return redirect($back)->with('info', 'Aucune carte à réviser pour le moment !');
        }

        return view('student.flashcards.study', compact('dueCards'));
    }

    public function review(Request $request, Flashcard $flashcard)
    {
        $this->authorize('review', $flashcard);

        $request->validate(['quality' => 'required|integer|min:0|max:5']);
        $this->service->review($flashcard, (int) $request->input('quality'));

        return response()->json(['ok' => true]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
            'sub_chapter_id' => 'nullable|exists:sub_chapters,id',
        ]);

        if (! empty($data['sub_chapter_id'])) {
            $subchapter = SubChapter::with('chapter.formation')->findOrFail($data['sub_chapter_id']);
            if (! $subchapter->chapter?->formation) {
                abort(404);
            }
            $this->ensureEnrolled(auth()->user(), $subchapter->chapter->formation);
        }

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
        $this->authorize('update', $flashcard);

        $flashcard->update($request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
        ]));

        return back()->with('success', 'Flashcard mise à jour.');
    }

    public function destroy(Flashcard $flashcard)
    {
        $this->authorize('delete', $flashcard);

        $flashcard->delete();

        return back()->with('success', 'Flashcard supprimée.');
    }

    public function generate(SubChapter $subchapter)
    {
        $subchapter->load('chapter.formation');
        $formation = $subchapter->chapter?->formation;
        if (! $formation) {
            abort(404);
        }
        $this->ensureEnrolled(auth()->user(), $formation);

        $created = $this->service->generateFromSubChapter($subchapter, auth()->user());

        return back()->with(
            empty($created) ? 'error' : 'success',
            empty($created)
                ? 'Impossible de générer des flashcards. Réessayez dans un instant.'
                : count($created).' flashcard(s) générée(s).'
        );
    }

    private function ensureEnrolled($user, Formation $formation): void
    {
        if (! $formation->students()->where('user_id', $user->id)->exists()) {
            abort(403, 'Vous n\'êtes pas inscrit à cette formation.');
        }
    }
}
