<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Policy class for managing permissions related to Note model. 
 * It defines who can view, create, update, or delete notes based on user roles and ownership.
 */

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Note $note): bool
    {
        return $user->isAdmin() || $note->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Note $note): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->isAdmin();
    }
}
