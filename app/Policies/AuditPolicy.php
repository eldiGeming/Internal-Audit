<?php

namespace App\Policies;

use App\Models\Audit;
use App\Models\User;

/**
 * Admins can see and manage every audit; auditors only the audits they own.
 */
class AuditPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->isAdmin() || $audit->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Audit $audit): bool
    {
        return $this->view($user, $audit);
    }

    public function delete(User $user, Audit $audit): bool
    {
        return $this->view($user, $audit);
    }

    /**
     * Deleting a CAR from the admin CAR register (it also marks the finding as "Sesuai").
     */
    public function deleteCorrectiveAction(User $user, Audit $audit): bool
    {
        return $user->isAdmin();
    }
}
