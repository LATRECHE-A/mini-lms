<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing quizzes associated with sub-chapters in the admin panel.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuizRequest;
use App\Models\Quiz;
use App\Models\SubChapter;

class QuizController extends Controller
{
    public function index()
    {
        $search = request('search');

        $quizzes = Quiz::with('subChapter.chapter.formation')
            ->withCount(['questions', 'attempts'])
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('subChapter', fn($sq) => $sq->where('title', 'like', "%{$search}%"));
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create(SubChapter $subchapter)
    {
        // Prevent creating duplicate quiz for same subchapter
        if ($subchapter->quiz) {
            return redirect()
                ->route('admin.quizzes.show', $subchapter->quiz)
                ->with('info', 'Ce sous-chapitre a déjà un quiz.');
        }

        $subchapter->load('chapter.formation');
        return view('admin.quizzes.create', compact('subchapter'));
    }

    public function store(QuizRequest $request, SubChapter $subchapter)
    {
        if ($subchapter->quiz) {
            return redirect()
                ->route('admin.quizzes.show', $subchapter->quiz)
                ->with('error', 'Un quiz existe déjà pour ce sous-chapitre.');
        }

        $quiz = $subchapter->quiz()->create($request->validated());

        return redirect()
            ->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz créé. Ajoutez des questions.');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load([
            'subChapter.chapter.formation',
            'questions.answers',
            'attempts' => fn($q) => $q->with('user')->whereNotNull('completed_at')->orderByDesc('completed_at')->limit(20),
        ]);

        return view('admin.quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        $quiz->load('subChapter.chapter.formation');
        return view('admin.quizzes.edit', compact('quiz'));
    }

    public function update(QuizRequest $request, Quiz $quiz)
    {
        $quiz->update($request->validated());

        return redirect()
            ->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz mis à jour.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->load('subChapter.chapter');
        $formationId = $quiz->subChapter?->chapter?->formation_id;
        $quiz->delete();

        return redirect()
            ->route($formationId ? 'admin.formations.show' : 'admin.quizzes.index', $formationId)
            ->with('success', 'Quiz supprimé.');
    }
}
