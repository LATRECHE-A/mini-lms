<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing sub-chapters: CRUD + image upload (AJAX).
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubChapterRequest;
use App\Models\Chapter;
use App\Models\SubChapter;
use Illuminate\Http\Request;

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

        return redirect()->route('admin.formations.show', $chapter->formation_id)->with('success', 'Sous-chapitre ajouté.');
    }

    public function show(Chapter $chapter, SubChapter $subchapter)
    {
        $chapter->load('formation');
        $subchapter->load('quiz.questions.answers');

        return view('admin.subchapters.show', compact('chapter', 'subchapter'));
    }

    public function edit(Chapter $chapter, SubChapter $subchapter)
    {
        $chapter->load('formation');

        return view('admin.subchapters.edit', compact('chapter', 'subchapter'));
    }

    public function update(SubChapterRequest $request, Chapter $chapter, SubChapter $subchapter)
    {
        $subchapter->update($request->validated());

        return redirect()->route('admin.formations.show', $chapter->formation_id)->with('success', 'Sous-chapitre mis à jour.');
    }

    public function destroy(Chapter $chapter, SubChapter $subchapter)
    {
        $subchapter->delete();

        return redirect()->route('admin.formations.show', $chapter->formation_id)->with('success', 'Sous-chapitre supprimé.');
    }

    /**
     * AJAX image upload - stores in public/uploads/, returns URL.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ], [
            'image.required' => 'Aucun fichier sélectionné.',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => "L'image ne doit pas dépasser 5 Mo.",
        ]);

        $file = $request->file('image');
        $name = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $file->move(public_path('uploads'), $name);

        return response()->json(['url' => asset('uploads/'.$name)]);
    }
}
