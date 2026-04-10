<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing sub-chapters within chapters in the admin panel.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubChapterRequest;
use App\Models\Chapter;
use App\Models\SubChapter;

class SubChapterController extends Controller
{
    public function create(Chapter $chapter)
    {
        $chapter->load('formation');
        $nextOrder = $chapter->subChapters()->max('order') + 1;
        return view('admin.subchapters.create', compact('chapter', 'nextOrder'));
    }

    public function store(SubChapterRequest $request, Chapter $chapter)
    {
        $data = $request->validated();
        $data['order'] = $data['order'] ?? ($chapter->subChapters()->max('order') + 1);

        $chapter->subChapters()->create($data);

        return redirect()
            ->route('admin.formations.show', $chapter->formation_id)
            ->with('success', 'Sous-chapitre ajouté.');
    }

    public function edit(Chapter $chapter, SubChapter $subchapter)
    {
        $chapter->load('formation');
        return view('admin.subchapters.edit', compact('chapter', 'subchapter'));
    }

    public function update(SubChapterRequest $request, Chapter $chapter, SubChapter $subchapter)
    {
        $subchapter->update($request->validated());

        return redirect()
            ->route('admin.formations.show', $chapter->formation_id)
            ->with('success', 'Sous-chapitre mis à jour.');
    }

    public function destroy(Chapter $chapter, SubChapter $subchapter)
    {
        $subchapter->delete();

        return redirect()
            ->route('admin.formations.show', $chapter->formation_id)
            ->with('success', 'Sous-chapitre supprimé.');
    }

    public function show(Chapter $chapter, SubChapter $subchapter)
    {
        $chapter->load('formation');
        $subchapter->load('quiz.questions.answers');
        return view('admin.subchapters.show', compact('chapter', 'subchapter'));
    }
}
