<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student sub-chapter CRUD - only for sub-chapters whose parent formation
 * the student owns. Authorization is enforced via SubChapterPolicy on
 * every action.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubChapterRequest;
use App\Models\Chapter;
use App\Models\SubChapter;
use App\Services\ContentSanitizer;
use Illuminate\Http\Request;

class SubChapterController extends Controller
{
    public function create(Chapter $chapter)
    {
        $chapter->load('formation');
        $formation = $chapter->formation;
        if (! $formation) {
            abort(404);
        }
        $this->authorize('update', $formation);

        $nextOrder = $chapter->subChapters()->max('order') + 1;

        return view('student.subchapters.create', compact('chapter', 'formation', 'nextOrder'));
    }

    public function store(SubChapterRequest $request, Chapter $chapter)
    {
        // FormRequest authorizes via FormationPolicy on the chapter's parent.
        $data = $request->validated();
        $data['order'] = $data['order'] ?? ($chapter->subChapters()->max('order') + 1);

        $chapter->subChapters()->create($data);

        return redirect()
            ->route('student.formations.show', $chapter->formation_id)
            ->with('success', 'Sous-chapitre ajouté.');
    }

    public function edit(SubChapter $subchapter)
    {
        $this->authorize('update', $subchapter);

        $subchapter->load('chapter.formation');
        $formation = $subchapter->chapter->formation;

        return view('student.formations.edit-subchapter', compact('subchapter', 'formation'));
    }

    public function update(Request $request, SubChapter $subchapter)
    {
        $this->authorize('update', $subchapter);

        $subchapter->load('chapter.formation');
        $formation = $subchapter->chapter->formation;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:65000'],
        ]);

        $subchapter->update([
            'title' => strip_tags($data['title']),
            'content' => ContentSanitizer::render($data['content'] ?? ''),
        ]);

        return redirect()
            ->route('student.formations.subchapter', [$formation, $subchapter])
            ->with('success', 'Contenu mis à jour.');
    }

    public function destroy(SubChapter $subchapter)
    {
        $this->authorize('delete', $subchapter);

        $subchapter->load('chapter.formation');
        $formation = $subchapter->chapter->formation;
        $subchapter->delete();

        return redirect()
            ->route('student.formations.show', $formation)
            ->with('success', 'Sous-chapitre supprimé.');
    }
}
