<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing student notes in the admin panel.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteRequest;
use App\Models\Formation;
use App\Models\Note;
use App\Models\User;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::with(['user', 'formation'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.notes.index', compact('notes'));
    }

    public function create()
    {
        $students = User::students()->orderBy('name')->get();
        $formations = Formation::orderBy('name')->get();
        return view('admin.notes.create', compact('students', 'formations'));
    }

    public function store(NoteRequest $request)
    {
        Note::create($request->validated());

        return redirect()
            ->route('admin.notes.index')
            ->with('success', 'Note enregistrée.');
    }

    public function edit(Note $note)
    {
        $students = User::students()->orderBy('name')->get();
        $formations = Formation::orderBy('name')->get();
        return view('admin.notes.edit', compact('note', 'students', 'formations'));
    }

    public function update(NoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        return redirect()
            ->route('admin.notes.index')
            ->with('success', 'Note mise à jour.');
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()
            ->route('admin.notes.index')
            ->with('success', 'Note supprimée.');
    }
}
