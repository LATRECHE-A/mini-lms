<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student subchapter editing - students can ONLY edit formations they created
 * (via AI validation). Cannot edit admin-created formations.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\SubChapter;
use App\Services\ContentSanitizer;
use Illuminate\Http\Request;

class SubChapterController extends Controller
{
    /**
     * Authorize: student must be enrolled AND must be the formation creator.
     */
    private function authorizeOwnership(SubChapter $subchapter): Formation
    {
        $formation = $subchapter->chapter?->formation;
        if (! $formation) {
            abort(404);
        }

        $user = auth()->user();

        // Must be enrolled
        if (! $formation->students()->where('user_id', $user->id)->exists()) {
            abort(403, 'Vous n\'êtes pas inscrit à cette formation.');
        }

        // Must be the creator (only edit own AI-generated formations)
        if (! $formation->isOwnedBy($user)) {
            abort(403, 'Vous ne pouvez modifier que vos propres formations.');
        }

        return $formation;
    }

    public function edit(SubChapter $subchapter)
    {
        $formation = $this->authorizeOwnership($subchapter);
        $subchapter->load('chapter');

        return view('student.formations.edit-subchapter', compact('subchapter', 'formation'));
    }

    public function update(Request $request, SubChapter $subchapter)
    {
        $formation = $this->authorizeOwnership($subchapter);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:65000'],
        ]);

        $subchapter->update([
            'title' => strip_tags($data['title']),
            'content' => ContentSanitizer::render($data['content'] ?? ''),
        ]);

        return redirect()->route('student.formations.subchapter', [$formation, $subchapter])
            ->with('success', 'Contenu mis à jour.');
    }
}
