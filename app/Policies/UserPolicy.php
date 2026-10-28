<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    /**
     * Grant admins and super-admins all permissions automatically.
     * Returning true here short-circuits all other policy checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            return true;
        }

        return null; // fall through to individual methods for other roles
    }

    /**
     * Determine whether the user can view the user list.
     */
    public function viewAny(User $admin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create new users.
     */
    public function create(User $admin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update another user.
     */
    public function update(User $admin, User $target): bool
    {
        return false;
    }

    /**
     * Determine whether the admin can update the target user's role.
     * Admin cannot change their own role.
     */
    public function updateRole(User $admin, User $target): bool
    {
        if ($admin->id === $target->id) {
            return false;
        }

        return false;
    }

    /**
     * Determine whether the admin can update the target user's status.
     */
    public function updateStatus(User $admin, User $target): bool
    {
        return false;
    }

    /**
     * Determine whether the admin can approve a pending instructor.
     */
    public function approveInstructor(User $admin, User $target): bool
    {
        return false;
    }
}
