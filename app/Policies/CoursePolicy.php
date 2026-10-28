<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view any courses in administration.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(?User $user, Course $course): bool
    {
        if ($course->isPublished()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSuperAdmin()
            || ($user->isInstructor() && $course->instructor_id === $user->id);
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstructor() && $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstructor() && $course->instructor_id === $user->id;
    }

    /**
     * Determine whether an instructor can submit the course for review.
     */
    public function submitForApproval(User $user, Course $course): bool
    {
        return ($course->instructor_id === $user->id || $user->isAdmin() || $user->isSuperAdmin())
            && in_array($course->approval_status, [Course::APPROVAL_DRAFT, Course::APPROVAL_REJECTED], true);
    }

    /**
     * Only Admin or Super Admin can approve courses. Instructors cannot publish directly.
     */
    public function approve(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Only Admin or Super Admin can reject courses.
     */
    public function reject(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Only Admin or Super Admin can unpublish courses.
     */
    public function unpublish(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
