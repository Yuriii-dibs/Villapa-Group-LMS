<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Determine whether the user can view the lesson content.
     * Enforces: Student cannot read a lesson without enrolling (unless it's a preview).
     */
    public function view(User $user, Lesson $lesson): bool
    {
        $course = $lesson->module->course;

        // Admin, Super Admin, or Course Instructor can always view
        if ($user->isAdmin() || $user->isSuperAdmin() || $course->instructor_id === $user->id) {
            return true;
        }

        // Preview lessons can be viewed by anyone authenticated
        if ($lesson->is_preview) {
            return true;
        }

        // Student must have an active enrollment in this course
        return CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [CourseEnrollment::STATUS_ACTIVE, CourseEnrollment::STATUS_COMPLETED])
            ->exists();
    }

    /**
     * Determine whether the user can create lessons for a course.
     */
    public function create(User $user, Course $course): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstructor() && $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        $course = $lesson->module->course;

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstructor() && $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        $course = $lesson->module->course;

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstructor() && $course->instructor_id === $user->id;
    }
}
