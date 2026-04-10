<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Policy class to manage permissions for AI generation resources based on user roles (admin and student) and ownership.
 */

namespace App\Policies;

use App\Models\AiGeneration;
use App\Models\User;

class AiGenerationPolicy
{
    /**
     * Admin sees all; student sees only their own.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin can view any generation.
     * Student can only view their own.
     */
    public function view(User $user, AiGeneration $generation): bool
    {
        if ($user->isAdmin()) return true;

        return $generation->isOwnedBy($user);
    }

    /**
     * Both roles can create generations.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Edit: only the owner, and only while content is still a draft.
     * Admin cannot edit student drafts. Student cannot edit admin drafts.
     */
    public function update(User $user, AiGeneration $generation): bool
    {
        return $generation->isOwnedBy($user) && $generation->isEditable();
    }

    /**
     * Delete: owner can delete their own content.
     * Admin can delete any generation.
     */
    public function delete(User $user, AiGeneration $generation): bool
    {
        if ($user->isAdmin()) return true;

        return $generation->isOwnedBy($user);
    }

    /**
     * Validate (student workflow): only the owner student, only drafts.
     * Admin does not "validate" - admin "publishes" via import.
     */
    public function validate(User $user, AiGeneration $generation): bool
    {
        return $user->isStudent()
            && $generation->isOwnedBy($user)
            && $generation->isDraft();
    }

    /**
     * Import into formation: only admin, only drafts.
     */
    public function import(User $user, AiGeneration $generation): bool
    {
        return $user->isAdmin() && $generation->isDraft();
    }

    /**
     * Regenerate: only the owner.
     */
    public function regenerate(User $user, AiGeneration $generation): bool
    {
        return $generation->isOwnedBy($user);
    }
}
