<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectCourseRequest;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseApprovalController extends Controller
{
    /**
     * List all courses awaiting approval.
     */
    public function index(): View
    {
        $pendingCourses = Course::where('approval_status', Course::APPROVAL_PENDING)
            ->with(['instructor', 'category'])
            ->withCount('lessons')
            ->latest()
            ->paginate(10);

        return view('admin.courses.pending', [
            'pendingCourses' => $pendingCourses,
        ]);
    }

    /**
     * Approve a course: marks it approved, published, and sets published_at timestamp.
     */
    public function approve(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('approve', $course);

        DB::transaction(function () use ($course, $request) {
            $course->update([
                'approval_status' => Course::APPROVAL_APPROVED,
                'publication_status' => Course::PUB_PUBLISHED,
                'published_at' => now(),
            ]);

            CourseReview::create([
                'course_id' => $course->id,
                'reviewer_id' => $request->user()->id,
                'decision' => CourseReview::DECISION_APPROVED,
                'reason' => 'Approved for public enrollment.',
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('status', "Course [{$course->title}] has been approved and published to the catalog.");
    }

    /**
     * Reject a course with a required reason.
     */
    public function reject(RejectCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('reject', $course);

        $reason = $request->validated('reason');

        DB::transaction(function () use ($course, $request, $reason) {
            $course->update([
                'approval_status' => Course::APPROVAL_REJECTED,
                'publication_status' => Course::PUB_DRAFT,
            ]);

            CourseReview::create([
                'course_id' => $course->id,
                'reviewer_id' => $request->user()->id,
                'decision' => CourseReview::DECISION_REJECTED,
                'reason' => $reason,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('status', "Course [{$course->title}] has been rejected with feedback provided.");
    }

    /**
     * Unpublish an active course.
     */
    public function unpublish(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('unpublish', $course);

        $course->update([
            'publication_status' => Course::PUB_DRAFT,
        ]);

        return back()->with('status', "Course [{$course->title}] has been unpublished from the catalog.");
    }

    /**
     * Display all courses across the LMS for administrative oversight.
     */
    public function allCourses(Request $request): View
    {
        $this->authorize('viewAny', Course::class);

        $query = Course::with(['instructor', 'category'])
            ->withCount(['lessons', 'enrollments'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if (in_array($status, [Course::PUB_PUBLISHED, Course::PUB_DRAFT, Course::PUB_ARCHIVED])) {
                $query->where('publication_status', $status);
            }
        }

        if ($request->filled('approval')) {
            $approval = $request->input('approval');
            if (in_array($approval, [Course::APPROVAL_PENDING, Course::APPROVAL_APPROVED, Course::APPROVAL_REJECTED])) {
                $query->where('approval_status', $approval);
            }
        }

        $courses = $query->paginate(15)->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'currentSearch' => $request->input('search', ''),
            'currentStatus' => $request->input('status', ''),
            'currentApproval' => $request->input('approval', ''),
        ]);
    }
}

