<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    /**
     * Students are not permitted to self-enroll, add, or change programs.
     * All degree programs and courses are assigned exclusively by school administrators.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();

        // Students cannot self-enroll or alter their degree program
        if (! $user->isAdmin()) {
            return back()->withErrors([
                'error' => 'Self-enrollment is disabled. Students do not have permission to add or change their degree program. Please contact the administrator or registrar.',
            ]);
        }

        if (! $course->isPublished()) {
            return back()->withErrors(['error' => 'This course is not open for enrollment.']);
        }

        // Check if student is already enrolled
        $existing = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.courses.show', $course)
                ->with('status', 'You are already enrolled in this course.');
        }

        DB::transaction(function () use ($user, $course) {
            CourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => CourseEnrollment::STATUS_ACTIVE,
                'enrolled_at' => now(),
            ]);
        });

        return redirect()->route('student.courses.show', $course)
            ->with('status', "Enrolled successfully in [{$course->title}]!");
    }
}
