<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for students to view their enrolled formations and access chapters and subchapters.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\SubChapter;

class FormationController extends Controller
{
    public function index()
    {
        $formations = auth()->user()->formations()
            ->published()
            ->withCount('chapters')
            ->orderBy('name')
            ->get();

        return view('student.formations.index', compact('formations'));
    }

    public function show(Formation $formation)
    {
        // Authorization: student must be enrolled
        if (!$formation->students()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Vous n\'êtes pas inscrit à cette formation.');
        }

        if ($formation->status !== 'published') {
            abort(404);
        }

        $formation->load(['chapters.subChapters.quiz']);

        return view('student.formations.show', compact('formation'));
    }

    public function showSubChapter(Formation $formation, SubChapter $subchapter)
    {
        if (!$formation->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        if ($formation->status !== 'published') {
            abort(404);
        }

        // Eager load to avoid N+1
        $subchapter->load(['chapter.formation', 'quiz.questions']);

        // Verify subchapter actually belongs to this formation (IDOR prevention)
        if ($subchapter->chapter?->formation_id !== $formation->id) {
            abort(404);
        }

        $personalNotes = auth()->user()->personalNotes()
            ->where('sub_chapter_id', $subchapter->id)
            ->orderByDesc('updated_at')
            ->get();

        return view('student.formations.subchapter', compact('formation', 'subchapter', 'personalNotes'));
    }
}
