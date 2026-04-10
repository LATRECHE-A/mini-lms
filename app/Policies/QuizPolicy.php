<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Policy class to manage permissions for Quiz model. 
 * Defines who can view, create, update, delete, and attempt quizzes based on user roles and quiz status.
 */

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->isAdmin()) return true;

        // Student can view published quizzes in formations they're enrolled in
        $formation = $quiz->subChapter->chapter->formation;
        return $quiz->status === 'published'
            && $formation->students()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin();
    }

    public function attempt(User $user, Quiz $quiz): bool
    {
        if (!$user->isStudent()) return false;
        if ($quiz->status !== 'published') return false;

        $formation = $quiz->subChapter->chapter->formation;
        return $formation->students()->where('user_id', $user->id)->exists();
    }
}
