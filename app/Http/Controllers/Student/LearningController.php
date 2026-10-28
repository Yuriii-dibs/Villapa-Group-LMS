<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LearningController extends Controller
{
    /**
     * Display enrolled student's course view with progress and syllabus.
     */
    public function showCourse(Request $request, Course $course): View
    {
        $user = $request->user();

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment && ! $user->isAdmin() && ! $user->isSuperAdmin() && $course->instructor_id !== $user->id) {
            abort(403, 'You must be enrolled in this course to access the classroom.');
        }

        $course->load([
            'category',
            'instructor',
            'modules.lessons',
            'classSessions' => function ($q) {
                $q->orderBy('starts_at', 'asc');
            },
        ]);

        $completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        $progressPercentage = $enrollment ? $enrollment->progressPercentage() : 0;

        return view('student.courses.show', [
            'course' => $course,
            'enrollment' => $enrollment,
            'completedLessonIds' => $completedLessonIds,
            'progressPercentage' => $progressPercentage,
        ]);
    }

    /**
     * View lesson content. Enforces LessonPolicy (student must be enrolled unless preview).
     */
    public function showLesson(Request $request, Course $course, Lesson $lesson): View
    {
        $this->authorize('view', $lesson);

        $user = $request->user();
        $course->load(['modules.lessons']);

        $isCompleted = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->where('is_completed', true)
            ->exists();

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $progressPercentage = $enrollment ? $enrollment->progressPercentage() : 0;

        return view('student.lessons.show', [
            'course' => $course,
            'lesson' => $lesson,
            'isCompleted' => $isCompleted,
            'progressPercentage' => $progressPercentage,
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Mark a lesson as complete. Safe to click twice (idempotent).
     * Recalculates progress and sets enrollments.completed_at when 100%.
     */
    public function completeLesson(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        $course = $lesson->module->course;

        // Ensure user is enrolled
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        DB::transaction(function () use ($user, $lesson, $enrollment) {
            // Idempotent record update
            LessonProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now(),
                ]
            );

            // Re-calculate course progress and update completed_at if 100%
            $enrollment->syncCompletion();
        });

        return back()->with('status', 'Lesson marked as completed!');
    }
}
