<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service class to aggregate and provide dashboard metrics for both admin and student users.
 */

namespace App\Services;

use App\Models\Formation;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get admin dashboard metrics.
     */
    public function getAdminMetrics(): array
    {
        return [
            'total_students' => User::students()->count(),
            'total_formations' => Formation::count(),
            'published_formations' => Formation::published()->count(),
            'total_quiz_attempts' => QuizAttempt::whereNotNull('completed_at')->count(),
            'avg_quiz_score' => round(QuizAttempt::whereNotNull('completed_at')->avg('percentage') ?? 0, 1),
            'recent_attempts' => QuizAttempt::with(['user', 'quiz.subChapter'])
                ->whereNotNull('completed_at')
                ->orderByDesc('completed_at')
                ->limit(10)
                ->get(),
            'recent_students' => User::students()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Get student dashboard metrics.
     */
    public function getStudentMetrics(User $user): array
    {
        $enrolledFormations = $user->formations()
            ->withCount('chapters')
            ->published()
            ->get();

        $completedAttempts = $user->quizAttempts()
            ->whereNotNull('completed_at')
            ->with('quiz.subChapter.chapter.formation')
            ->orderByDesc('completed_at')
            ->get();

        $avgScore = $completedAttempts->avg('percentage');
        $totalQuizzes = $completedAttempts->count();

        $recentNotes = $user->notes()
            ->with('formation')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingTodos = $user->todos()
            ->pending()
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return [
            'enrolled_formations' => $enrolledFormations,
            'completed_quizzes' => $totalQuizzes,
            'avg_score' => round($avgScore ?? 0, 1),
            'best_score' => $completedAttempts->max('percentage') ?? 0,
            'recent_attempts' => $completedAttempts->take(5),
            'recent_notes' => $recentNotes,
            'pending_todos' => $pendingTodos,
        ];
    }
}
