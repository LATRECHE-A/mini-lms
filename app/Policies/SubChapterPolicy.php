<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Sub-chapter authorization mirrors FormationPolicy: a student may edit a
 * sub-chapter iff they own the parent formation.
 */

namespace App\Policies;

use App\Models\SubChapter;
use App\Models\User;

class SubChapterPolicy
{
    public function view(User $user, SubChapter $subchapter): bool
    {
        $formation = $subchapter->chapter?->formation;
        if (! $formation) {
            return false;
        }

        if ($user->isAdmin() || $formation->isOwnedBy($user)) {
            return true;
        }

        return $formation->status === 'published'
            && $formation->students()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, SubChapter $subchapter): bool
    {
        $formation = $subchapter->chapter?->formation;
        if (! $formation) {
            return false;
        }

        return $user->isAdmin() || $formation->isOwnedBy($user);
    }

    public function delete(User $user, SubChapter $subchapter): bool
    {
        return $this->update($user, $subchapter);
    }
}
