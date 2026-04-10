<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service class to handle quiz attemts, scoring, and history retrieval. 
 * Ensures data integrity and security through transactions and validation.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizService
{
    /**
     * Start a new quiz attempt. Prevents concurrent active attempts.
     * Uses pessimistic locking to prevent race conditions.
     */
    public function startAttempt(User $user, Quiz $quiz): QuizAttempt
    {
        return DB::transaction(function () use ($user, $quiz) {
            // Pessimistic lock: prevent race condition where two requests
            // both see no active attempt and both create one
            $active = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->whereNull('completed_at')
                ->lockForUpdate()
                ->first();

            if ($active) {
                return $active;
            }

            return QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'total_questions' => $quiz->questions()->count(),
                'started_at' => now(),
            ]);
        });
    }

    /**
     * Submit and score a quiz attempt. Idempotent — returns existing result if already completed.
     *
     * Security: validates that every submitted answer ID belongs to a question
     * within this quiz. Prevents answer ID tampering via devtools.
     *
     * @param QuizAttempt $attempt
     * @param array $submittedAnswers ['question_id' => 'answer_id', ...]
     * @return QuizAttempt
     * @throws ValidationException
     */
    public function submitAttempt(QuizAttempt $attempt, array $submittedAnswers): QuizAttempt
    {
        // Idempotent: if already completed, return as-is
        if ($attempt->isCompleted()) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt, $submittedAnswers) {
            // Lock the attempt row to prevent concurrent submissions
            $attempt = QuizAttempt::where('id', $attempt->id)
                ->lockForUpdate()
                ->first();

            // Double-check after lock
            if ($attempt->isCompleted()) {
                return $attempt;
            }

            $quiz = $attempt->quiz()->with('questions.answers')->first();

            // Build a set of valid answer IDs for this quiz (security)
            $validAnswerIds = $quiz->questions
                ->flatMap(fn($q) => $q->answers->pluck('id'))
                ->all();

            $score = 0;
            $total = $quiz->questions->count();
            $answersGiven = [];

            foreach ($quiz->questions as $question) {
                $submittedAnswerId = $submittedAnswers[$question->id] ?? null;

                // SECURITY: Verify submitted answer belongs to THIS question
                if ($submittedAnswerId !== null) {
                    $submittedAnswerId = (int) $submittedAnswerId;
                    $belongsToQuestion = $question->answers->contains('id', $submittedAnswerId);

                    if (!$belongsToQuestion) {
                        // Tampered answer — treat as unanswered, don't throw
                        // (throwing would leak information about valid IDs)
                        $submittedAnswerId = null;
                    }
                }

                $correctAnswer = $question->answers->firstWhere('is_correct', true);
                $isCorrect = $correctAnswer && $submittedAnswerId === $correctAnswer->id;

                if ($isCorrect) {
                    $score++;
                }

                $answersGiven[$question->id] = [
                    'submitted' => $submittedAnswerId,
                    'correct' => $correctAnswer?->id,
                    'is_correct' => $isCorrect,
                ];
            }

            $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;

            $attempt->update([
                'score' => $score,
                'total_questions' => $total,
                'percentage' => $percentage,
                'answers_given' => $answersGiven,
                'completed_at' => now(),
            ]);

            ActivityLog::log($attempt->user_id, 'quiz.completed', $attempt->quiz, [
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Get attempt history for a user on a specific quiz.
     */
    public function getAttemptHistory(User $user, Quiz $quiz)
    {
        return QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * Get the best attempt for a user on a quiz.
     */
    public function getBestAttempt(User $user, Quiz $quiz): ?QuizAttempt
    {
        return QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('percentage')
            ->first();
    }
}
