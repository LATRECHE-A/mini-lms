<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing quiz interactions for students.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private QuizService $quizService) {}

    public function index()
    {
        $attempts = auth()->user()->quizAttempts()
            ->with('quiz.subChapter.chapter.formation')
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->paginate(15);

        return view('student.quizzes.index', compact('attempts'));
    }

    public function show(Quiz $quiz)
    {
        // Eager load FIRST to avoid N+1 on authorization check
        $quiz->load('subChapter.chapter.formation');

        $formation = $quiz->subChapter?->chapter?->formation;
        if (!$formation || !$formation->students()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Vous n\'êtes pas inscrit à cette formation.');
        }

        if ($quiz->status !== 'published') {
            abort(404);
        }

        // SECURITY: Exclude is_correct from client payload; shuffle answers
        $questions = $quiz->questions()->with(['answers' => function ($q) {
            $q->select('id', 'question_id', 'answer_text')
              ->inRandomOrder();
        }])->orderBy('order')->get();

        $attempt = $this->quizService->startAttempt(auth()->user(), $quiz);
        $history = $this->quizService->getAttemptHistory(auth()->user(), $quiz);

        return view('student.quizzes.show', compact('quiz', 'questions', 'attempt', 'history'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $quiz->load('subChapter.chapter.formation');

        $formation = $quiz->subChapter?->chapter?->formation;
        if (!$formation || !$formation->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        if ($quiz->status !== 'published') {
            abort(404);
        }

        $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'min:1'],
            'attempt_id' => ['required', 'integer'],
        ]);

        // IDOR: verify attempt belongs to THIS user AND THIS quiz
        $attempt = QuizAttempt::where('id', $request->attempt_id)
            ->where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->firstOrFail();

        if ($attempt->isCompleted()) {
            return redirect()
                ->route('student.quizzes.result', $attempt)
                ->with('info', 'Ce quiz a déjà été soumis.');
        }

        $result = $this->quizService->submitAttempt($attempt, $request->answers);

        return redirect()
            ->route('student.quizzes.result', $result)
            ->with('success', 'Quiz terminé !');
    }

    public function result(QuizAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->load('quiz.questions.answers', 'quiz.subChapter.chapter.formation');

        return view('student.quizzes.result', compact('attempt'));
    }
}
