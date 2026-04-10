<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Policy class to manage permissions for Formation model. 
 * Admins have full access, while students can only view published formations they're enrolled in.
 */

namespace App\Policies;

use App\Models\Formation;
use App\Models\User;

class FormationPolicy
{
    /**
     * Admin can do everything. Students can only view published formations they're enrolled in.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Formation $formation): bool
    {
        if ($user->isAdmin()) return true;

        return $formation->status === 'published'
            && $formation->students()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Formation $formation): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Formation $formation): bool
    {
        return $user->isAdmin();
    }
}
