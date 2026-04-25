<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Formation CRUD + enrollment with flashcard cloning.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormationRequest;
use App\Models\ActivityLog;
use App\Models\Formation;
use App\Models\User;
use App\Services\FlashcardService;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function __construct(private FlashcardService $flashcardService) {}

    public function index(Request $request)
    {
        $formations = Formation::withCount(['chapters', 'students'])
            ->search($request->input('search'))
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.formations.index', compact('formations'));
    }

    public function create()
    {
        return view('admin.formations.create');
    }

    public function store(FormationRequest $request)
    {
        $formation = Formation::create($request->validated());
        ActivityLog::log(auth()->id(), 'formation.created', $formation);

        return redirect()->route('admin.formations.show', $formation)->with('success', 'Formation créée.');
    }

    public function show(Formation $formation)
    {
        $formation->load(['chapters.subChapters.quiz', 'students', 'notes.user']);

        $availableStudents = User::students()
            ->whereNotIn('id', $formation->students->pluck('id'))
            ->orderBy('name')->get();

        return view('admin.formations.show', compact('formation', 'availableStudents'));
    }

    public function edit(Formation $formation)
    {
        return view('admin.formations.edit', compact('formation'));
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        $formation->update($request->validated());

        return redirect()->route('admin.formations.show', $formation)->with('success', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();

        return redirect()->route('admin.formations.index')->with('success', 'Formation supprimée.');
    }

    /**
     * Enroll a student — also clones template flashcards.
     */
    public function enroll(Request $request, Formation $formation)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);

        if (! $user->isStudent()) {
            return back()->with('error', 'Seuls les apprenants peuvent être inscrits.');
        }

        if ($formation->students()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet apprenant est déjà inscrit.');
        }

        $formation->students()->attach($user->id, ['enrolled_at' => now()]);

        // Clone template flashcards to student
        $cloned = $this->flashcardService->cloneTemplatesForStudent($formation, $user);

        ActivityLog::log(auth()->id(), 'formation.enrolled', $formation, [
            'student_id' => $user->id, 'student_name' => $user->name, 'flashcards_cloned' => $cloned,
        ]);

        $msg = "{$user->name} inscrit avec succès.";
        if ($cloned > 0) {
            $msg .= " {$cloned} flashcard(s) ajoutée(s).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Unenroll — removes unreviewed flashcards.
     */
    public function unenroll(Formation $formation, User $user)
    {
        $formation->students()->detach($user->id);
        $this->flashcardService->removeStudentCards($formation, $user);

        return back()->with('success', "{$user->name} désinscrit de la formation.");
    }
}
