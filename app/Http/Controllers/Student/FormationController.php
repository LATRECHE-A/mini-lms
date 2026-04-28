<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student formation access + editing parity.
 *
 * Read access (index, show, showSubChapter): published formations the
 * student is enrolled in, OR formations the student created themselves.
 *
 * Write access (edit, update, destroy): only formations the student owns
 * (created via AI workflow). Enforced uniformly by FormationPolicy.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormationRequest;
use App\Models\Formation;
use App\Models\SubChapter;

class FormationController extends Controller
{
    public function index()
    {
        // Show enrolled+published formations, plus any formations the
        // student authored (drafts included, since they own them).
        $userId = auth()->id();

        $enrolled = auth()->user()->formations()
            ->withCount('chapters')
            ->get();

        $authored = Formation::query()
            ->createdBy($userId)
            ->withCount('chapters')
            ->get();

        // Merge by id, prefer authored entry (richer status info).
        $formations = $authored->merge(
            $enrolled->reject(fn ($f) => $authored->contains('id', $f->id))
        )->sortBy('name')->values();

        return view('student.formations.index', compact('formations'));
    }

    public function show(Formation $formation)
    {
        $this->authorize('view', $formation);

        // For non-owned formations, also require published status (policy
        // already encodes this, but we double-check for clarity).
        if (! $formation->isOwnedBy(auth()->user()) && $formation->status !== 'published') {
            abort(404);
        }

        $formation->load(['chapters.subChapters.quiz']);

        return view('student.formations.show', compact('formation'));
    }

    public function showSubChapter(Formation $formation, SubChapter $subchapter)
    {
        $this->authorize('view', $formation);

        if (! $formation->isOwnedBy(auth()->user()) && $formation->status !== 'published') {
            abort(404);
        }

        $subchapter->load(['chapter.formation', 'quiz.questions']);

        // IDOR guard: subchapter must actually belong to this formation.
        if ($subchapter->chapter?->formation_id !== $formation->id) {
            abort(404);
        }

        $personalNotes = auth()->user()->personalNotes()
            ->where('sub_chapter_id', $subchapter->id)
            ->orderByDesc('updated_at')
            ->get();

        return view('student.formations.subchapter', compact('formation', 'subchapter', 'personalNotes'));
    }

    public function edit(Formation $formation)
    {
        $this->authorize('update', $formation);

        return view('student.formations.edit', compact('formation'));
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        // FormRequest already authorizes via FormationPolicy::update.
        $formation->update($request->validated());

        return redirect()
            ->route('student.formations.show', $formation)
            ->with('success', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $this->authorize('delete', $formation);

        $formation->delete();

        return redirect()
            ->route('student.formations.index')
            ->with('success', 'Formation supprimée.');
    }
}
