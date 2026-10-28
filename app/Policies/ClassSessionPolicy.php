<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\Role;
use App\Models\User;

class ClassSessionPolicy
{
    /**
     * Determine whether the user can view the session room/join page.
     */
    public function view(User $user, ClassSession $session): bool
    {
        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            return true;
        }

        if ($user->teachesCourse($session->course)) {
            return true;
        }

        return $user->isEnrolledIn($session->course);
    }

    /**
     * Determine whether the user can join the live class.
     * Enrolled students can only join if session is live or within 15 minutes before starts_at.
     */
    public function join(User $user, ClassSession $session): bool
    {
        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            return true;
        }

        if ($user->teachesCourse($session->course)) {
            return ! $session->isEnded();
        }

        if ($user->isEnrolledIn($session->course)) {
            return $session->canStudentJoin();
        }

        return false;
    }

    /**
     * Determine whether the user can manage (start, end, edit, delete) the session.
     * Only the instructor of the course can manage.
     */
    public function manage(User $user, ClassSession $session): bool
    {
        return $user->teachesCourse($session->course);
    }
}
