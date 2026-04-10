<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing chapters within formations in the admin panel.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChapterRequest;
use App\Models\Chapter;
use App\Models\Formation;

class ChapterController extends Controller
{
    public function create(Formation $formation)
    {
        $nextOrder = $formation->chapters()->max('order') + 1;
        return view('admin.chapters.create', compact('formation', 'nextOrder'));
    }

    public function store(ChapterRequest $request, Formation $formation)
    {
        $data = $request->validated();
        $data['order'] = $data['order'] ?? ($formation->chapters()->max('order') + 1);

        $formation->chapters()->create($data);

        return redirect()
            ->route('admin.formations.show', $formation)
            ->with('success', 'Chapitre ajouté.');
    }

    public function edit(Formation $formation, Chapter $chapter)
    {
        return view('admin.chapters.edit', compact('formation', 'chapter'));
    }

    public function update(ChapterRequest $request, Formation $formation, Chapter $chapter)
    {
        $chapter->update($request->validated());

        return redirect()
            ->route('admin.formations.show', $formation)
            ->with('success', 'Chapitre mis à jour.');
    }

    public function destroy(Formation $formation, Chapter $chapter)
    {
        $chapter->delete();

        return redirect()
            ->route('admin.formations.show', $formation)
            ->with('success', 'Chapitre supprimé.');
    }
}
