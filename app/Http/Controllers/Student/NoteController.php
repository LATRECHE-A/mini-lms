<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing personal notes for students.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalNoteRequest;
use App\Models\PersonalNote;

class NoteController extends Controller
{
    public function index()
    {
        $notes = auth()->user()->personalNotes()
            ->with('subChapter.chapter.formation')
            ->orderByDesc('updated_at')
            ->paginate(15);

        $grades = auth()->user()->notes()
            ->with('formation')
            ->orderByDesc('created_at')
            ->get();

        return view('student.notes.index', compact('notes', 'grades'));
    }

    public function store(PersonalNoteRequest $request)
    {
        auth()->user()->personalNotes()->create($request->validated());

        return back()->with('success', 'Note enregistrée.');
    }

    public function update(PersonalNoteRequest $request, PersonalNote $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->update($request->validated());

        return back()->with('success', 'Note mise à jour.');
    }

    public function destroy(PersonalNote $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->delete();

        return back()->with('success', 'Note supprimée.');
    }
}
