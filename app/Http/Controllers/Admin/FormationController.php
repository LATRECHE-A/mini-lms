<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing formations in the admin panel.
 * CRUD operations for formations, as well as enrolling and unenrolling students.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormationRequest;
use App\Models\ActivityLog;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Http\Request;

class FormationController extends Controller
{
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

        return redirect()
            ->route('admin.formations.show', $formation)
            ->with('success', 'Formation créée avec succès.');
    }

    public function show(Formation $formation)
    {
        $formation->load([
            'chapters.subChapters.quiz',
            'students',
            'notes.user',
        ]);

        $availableStudents = User::students()
            ->whereNotIn('id', $formation->students->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('admin.formations.show', compact('formation', 'availableStudents'));
    }

    public function edit(Formation $formation)
    {
        return view('admin.formations.edit', compact('formation'));
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        $formation->update($request->validated());

        return redirect()
            ->route('admin.formations.show', $formation)
            ->with('success', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();

        return redirect()
            ->route('admin.formations.index')
            ->with('success', 'Formation supprimée.');
    }

    /**
     * Enroll a student in a formation.
     */
    public function enroll(Request $request, Formation $formation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // SECURITY: Only students can be enrolled
        if (!$user->isStudent()) {
            return back()->with('error', 'Seuls les apprenants peuvent être inscrits.');
        }

        if ($formation->students()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet apprenant est déjà inscrit.');
        }

        $formation->students()->attach($user->id, ['enrolled_at' => now()]);

        ActivityLog::log(auth()->id(), 'formation.enrolled', $formation, [
            'student_id' => $user->id,
            'student_name' => $user->name,
        ]);

        return back()->with('success', "{$user->name} inscrit avec succès.");
    }

    /**
     * Remove a student from a formation.
     */
    public function unenroll(Formation $formation, User $user)
    {
        $formation->students()->detach($user->id);

        return back()->with('success', "{$user->name} désinscrit de la formation.");
    }
}
