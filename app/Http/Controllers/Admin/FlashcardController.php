<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Admin flashcards.
 *
 * Storage model:
 *   - Templates (is_template = true) are the master cards distributed to
 *     enrolled students.
 *   - For every template the admin owns, the service guarantees a personal
 *     copy exists. So the admin can ALWAYS test the cards in study mode,
 *     no matter how they were created.
 *
 * Authorization is enforced by FlashcardPolicy + the route group's
 * `role:admin` middleware. Ownership is checked on every write.
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

    public function index()
    {
        $user = auth()->user();
        $stats = $this->service->getStats($user);

        $formations = Formation::withCount(['chapters'])
            ->orderBy('name')
            ->get()
            ->map(function ($f) {
                $f->template_count = Flashcard::query()->templates()->forFormation($f->id)->count();

                return $f;
            });

        return view('admin.flashcards.index', compact('stats', 'formations'));
    }

    public function formation(Formation $formation)
    {
        $formation->load(['chapters.subChapters']);

        $user = auth()->user();
        $stats = $this->service->getStats($user, $formation->id);

        $subchapterIds = $formation->chapters->flatMap(fn ($ch) => $ch->subChapters->pluck('id'));

        $templateCounts = Flashcard::query()->templates()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        $personalCounts = Flashcard::query()->byUser($user->id)->personal()
            ->whereIn('sub_chapter_id', $subchapterIds)
            ->selectRaw('sub_chapter_id, count(*) as count')
            ->groupBy('sub_chapter_id')
            ->pluck('count', 'sub_chapter_id');

        return view('admin.flashcards.formation', compact('formation', 'stats', 'templateCounts', 'personalCounts'));
    }

    public function subchapter(SubChapter $subchapter)
    {
        $subchapter->load('chapter.formation');

        if (! $subchapter->chapter || ! $subchapter->chapter->formation) {
            abort(404);
        }

        $templates = Flashcard::query()->byUser(auth()->id())->templates()
            ->forSubChapter($subchapter->id)->orderBy('created_at')->get();

        $personal = Flashcard::query()->byUser(auth()->id())->personal()
            ->forSubChapter($subchapter->id)->orderBy('created_at')->get();

        return view('admin.flashcards.subchapter', compact('subchapter', 'templates', 'personal'));
    }

    /**
     * Study mode - runs on the admin's PERSONAL deck. The service
     * guarantees a personal copy exists for every template the admin owns
     * before fetching due cards, so this works even if templates were
     * created manually before the personal-copy invariant existed.
     */
    public function study(Request $request)
    {
        $formationId = $request->query('formation_id') ? (int) $request->query('formation_id') : null;
        $subChapterId = $request->query('sub_chapter_id') ? (int) $request->query('sub_chapter_id') : null;

        if ($subChapterId) {
            SubChapter::findOrFail($subChapterId);
        }
        if ($formationId) {
            Formation::findOrFail($formationId);
        }

        $dueCards = $this->service->getDueCards(auth()->user(), $formationId, $subChapterId, 20);

        if ($dueCards->isEmpty()) {
            $back = $subChapterId
                ? route('admin.flashcards.subchapter', $subChapterId)
                : ($formationId ? route('admin.flashcards.formation', $formationId) : route('admin.flashcards.index'));

            return redirect($back)->with('info', 'Aucune carte à réviser pour le moment.');
        }

        return view('admin.flashcards.study', compact('dueCards'));
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
            'sub_chapter_id' => 'required|exists:sub_chapters,id',
            'is_template' => 'nullable|boolean',
        ]);

        $isTemplate = (bool) ($data['is_template'] ?? true);

        $template = Flashcard::create([
            'user_id' => auth()->id(),
            'sub_chapter_id' => (int) $data['sub_chapter_id'],
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_template' => $isTemplate,
        ]);

        // If admin created a template, ensure a personal copy exists too.
        if ($isTemplate) {
            $this->service->ensurePersonalCopiesForAdmin(
                auth()->user(),
                null,
                $template->sub_chapter_id
            );
        }

        return back()->with('success', 'Flashcard créée.');
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        $this->authorize('update', $flashcard);

        $data = $request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:5000',
        ]);

        $oldQuestion = $flashcard->question;
        $flashcard->update($data);

        // Keep admin's personal copy in sync with their template.
        if ($flashcard->is_template) {
            $this->service->syncAdminPersonalAfterTemplateChange(auth()->user(), $flashcard, $oldQuestion);
        }

        return back()->with('success', 'Flashcard mise à jour.');
    }

    public function destroy(Flashcard $flashcard)
    {
        $this->authorize('delete', $flashcard);

        if ($flashcard->is_template) {
            $this->service->removeAdminPersonalForTemplate(auth()->user(), $flashcard);
        }

        $flashcard->delete();

        return back()->with('success', 'Flashcard supprimée.');
    }

    public function generate(SubChapter $subchapter)
    {
        $subchapter->load('chapter.formation');
        if (! $subchapter->chapter || ! $subchapter->chapter->formation) {
            abort(404);
        }

        $created = $this->service->generateFromSubChapter($subchapter, auth()->user());

        return back()->with(
            empty($created) ? 'error' : 'success',
            empty($created)
                ? 'Impossible de générer des flashcards. Réessayez dans un instant.'
                : count($created).' flashcard(s) générée(s).'
        );
    }
}
