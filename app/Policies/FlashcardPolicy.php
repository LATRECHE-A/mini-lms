<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Flashcard authorization.
 *
 * Ownership is strictly the `user_id`. Admins do NOT bypass this for
 * editing/reviewing - they manage their own templates and personal copies,
 * not anyone else's. This prevents IDOR even in the admin namespace.
 */

namespace App\Policies;

use App\Models\Flashcard;
use App\Models\User;

class FlashcardPolicy
{
    public function view(User $user, Flashcard $flashcard): bool
    {
        return $flashcard->user_id === $user->id;
    }

    public function update(User $user, Flashcard $flashcard): bool
    {
        return $flashcard->user_id === $user->id;
    }

    public function delete(User $user, Flashcard $flashcard): bool
    {
        return $flashcard->user_id === $user->id;
    }

    public function review(User $user, Flashcard $flashcard): bool
    {
        return $flashcard->user_id === $user->id && ! $flashcard->is_template;
    }
}
