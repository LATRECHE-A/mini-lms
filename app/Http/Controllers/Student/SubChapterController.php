<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Student subchapter editing - students can edit content of formations
 * they created via AI validation (enrollment check enforced).
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
     * Check that student is enrolled in the formation owning this subchapter.
     */
    private function authorizeAccess(SubChapter $subchapter): Formation
    {
        $formation = $subchapter->chapter?->formation;
        if (! $formation) {
            abort(404);
        }

        $enrolled = $formation->students()->where('user_id', auth()->id())->exists();
        if (! $enrolled) {
            abort(403, 'Vous n\'êtes pas inscrit à cette formation.');
        }

        return $formation;
    }

    public function edit(SubChapter $subchapter)
    {
        $formation = $this->authorizeAccess($subchapter);
        $subchapter->load('chapter');

        return view('student.formations.edit-subchapter', compact('subchapter', 'formation'));
    }

    public function update(Request $request, SubChapter $subchapter)
    {
        $this->authorizeAccess($subchapter);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:65000'],
        ]);

        $subchapter->update([
            'title' => strip_tags($data['title']),
            'content' => ContentSanitizer::render($data['content'] ?? ''),
        ]);

        $formation = $subchapter->chapter->formation;

        return redirect()->route('student.formations.subchapter', [$formation, $subchapter])
            ->with('success', 'Contenu mis à jour.');
    }
}
