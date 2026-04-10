<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing quiz questions in the admin panel.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function create(Quiz $quiz)
    {
        $quiz->load('subChapter.chapter.formation');
        $nextOrder = $quiz->questions()->max('order') + 1;
        return view('admin.questions.create', compact('quiz', 'nextOrder'));
    }

    public function store(QuestionRequest $request, Quiz $quiz)
    {
        DB::transaction(function () use ($request, $quiz) {
            $question = $quiz->questions()->create([
                'question_text' => $request->question_text,
                'order' => $quiz->questions()->max('order') + 1,
            ]);

            foreach ($request->answers as $index => $answerData) {
                $question->answers()->create([
                    'answer_text' => $answerData['text'],
                    'is_correct' => (int)$request->correct_answer === $index,
                ]);
            }
        });

        return redirect()
            ->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question ajoutée.');
    }

    public function edit(Quiz $quiz, Question $question)
    {
        $question->load('answers');
        $quiz->load('subChapter.chapter.formation');
        return view('admin.questions.edit', compact('quiz', 'question'));
    }

    public function update(QuestionRequest $request, Quiz $quiz, Question $question)
    {
        DB::transaction(function () use ($request, $question) {
            $question->update([
                'question_text' => $request->question_text,
            ]);

            // Delete old answers and create new ones
            $question->answers()->delete();

            foreach ($request->answers as $index => $answerData) {
                $question->answers()->create([
                    'answer_text' => $answerData['text'],
                    'is_correct' => (int)$request->correct_answer === $index,
                ]);
            }
        });

        return redirect()
            ->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question mise à jour.');
    }

    public function destroy(Quiz $quiz, Question $question)
    {
        $question->delete();

        return redirect()
            ->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question supprimée.');
    }
}
