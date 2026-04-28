<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student chapter CRUD - only for formations the student owns. Mirrors the
 * admin controller; authorization gates each action against the parent
 * formation via FormationPolicy::update.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChapterRequest;
use App\Models\Chapter;
use App\Models\Formation;

class ChapterController extends Controller
{
    public function create(Formation $formation)
    {
        $this->authorize('update', $formation);

        $nextOrder = $formation->chapters()->max('order') + 1;

        return view('student.chapters.create', compact('formation', 'nextOrder'));
    }

    public function store(ChapterRequest $request, Formation $formation)
    {
        // FormRequest already authorizes.
        $data = $request->validated();
        $data['order'] = $data['order'] ?? ($formation->chapters()->max('order') + 1);

        $formation->chapters()->create($data);

        return redirect()
            ->route('student.formations.show', $formation)
            ->with('success', 'Chapitre ajouté.');
    }

    public function edit(Formation $formation, Chapter $chapter)
    {
        $this->authorize('update', $formation);
        $this->ensureChapterBelongs($formation, $chapter);

        return view('student.chapters.edit', compact('formation', 'chapter'));
    }

    public function update(ChapterRequest $request, Formation $formation, Chapter $chapter)
    {
        $this->ensureChapterBelongs($formation, $chapter);
        $chapter->update($request->validated());

        return redirect()
            ->route('student.formations.show', $formation)
            ->with('success', 'Chapitre mis à jour.');
    }

    public function destroy(Formation $formation, Chapter $chapter)
    {
        $this->authorize('update', $formation);
        $this->ensureChapterBelongs($formation, $chapter);

        $chapter->delete();

        return redirect()
            ->route('student.formations.show', $formation)
            ->with('success', 'Chapitre supprimé.');
    }

    private function ensureChapterBelongs(Formation $formation, Chapter $chapter): void
    {
        if ($chapter->formation_id !== $formation->id) {
            abort(404);
        }
    }
}
