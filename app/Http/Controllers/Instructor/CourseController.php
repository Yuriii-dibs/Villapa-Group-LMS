<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = $request->user()->coursesAsInstructor()
            ->with(['category', 'reviews'])
            ->withCount(['modules', 'lessons', 'enrollments'])
            ->latest()
            ->paginate(10);

        return view('instructor.courses.index', [
            'courses' => $courses,
        ]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('instructor.courses.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $slug = Str::slug($validated['title']) . '-' . Str::lower(Str::random(6));

        $course = DB::transaction(function () use ($validated, $user, $thumbnailPath, $slug) {
            $course = Course::create([
                'instructor_id' => $user->id,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'],
                'difficulty' => $validated['difficulty'],
                'estimated_duration_minutes' => $validated['estimated_duration_minutes'] ?? null,
                'learning_objectives' => array_filter($validated['learning_objectives'] ?? []),
                'thumbnail_path' => $thumbnailPath,
                'approval_status' => Course::APPROVAL_DRAFT,
                'publication_status' => Course::PUB_DRAFT,
            ]);

            // Create initial module section for immediate lesson organization
            Module::create([
                'course_id' => $course->id,
                'title' => 'Course Content',
                'description' => 'Main module lessons and exercises.',
                'order' => 1,
                'is_published' => true,
            ]);

            return $course;
        });

        if ($request->input('action') === 'submit') {
            return redirect()->route('instructor.courses.edit', $course)
                ->with('status', 'Course draft created. Please add at least one lesson before submitting for administrator review.');
        }

        return redirect()->route('instructor.courses.edit', $course)
            ->with('status', 'Course draft created successfully. You may now add lessons.');
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['category', 'modules.lessons', 'reviews']);
        $categories = Category::orderBy('name')->get();

        return view('instructor.courses.edit', [
            'course' => $course,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validated();

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path && Storage::disk('public')->exists($course->thumbnail_path)) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $course->thumbnail_path = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->fill([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'difficulty' => $validated['difficulty'],
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'] ?? null,
            'learning_objectives' => array_filter($validated['learning_objectives'] ?? []),
        ]);

        // If course was rejected and is edited, reset to draft
        if ($course->approval_status === Course::APPROVAL_REJECTED) {
            $course->approval_status = Course::APPROVAL_DRAFT;
        }

        if ($request->input('action') === 'submit') {
            if ($course->lessons()->count() === 0) {
                $course->save();
                return back()->withErrors(['error' => 'Please add at least one lesson before submitting for approval.']);
            }

            $course->approval_status = Course::APPROVAL_PENDING;
            $course->save();

            return back()->with('status', 'Course details updated and submitted for administrator review.');
        }

        $course->save();

        return back()->with('status', 'Course details updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('instructor.courses.index')
            ->with('status', 'Course was archived/deleted successfully.');
    }

    /**
     * Submit a draft or rejected course for admin review.
     */
    public function submitForApproval(Course $course): RedirectResponse
    {
        $this->authorize('submitForApproval', $course);

        if ($course->lessons()->count() === 0) {
            return back()->withErrors(['error' => 'Please add at least one lesson before submitting for approval.']);
        }

        $course->update([
            'approval_status' => Course::APPROVAL_PENDING,
        ]);

        return back()->with('status', 'Course submitted for administrator review.');
    }

    /**
     * Display enrolled students list across the instructor's courses with progress.
     */
    public function students(Request $request): View
    {
        $user = $request->user();

        $courses = $user->coursesAsInstructor()->orderBy('title')->get();

        $query = CourseEnrollment::whereHas('course', function ($q) use ($user) {
            $q->where('instructor_id', $user->id);
        })->with(['user', 'course.category', 'course.modules.lessons']);

        if ($courseId = $request->input('course_id')) {
            $query->where('course_id', $courseId);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->latest('enrolled_at')->paginate(15)->withQueryString();

        return view('instructor.students.index', [
            'enrollments' => $enrollments,
            'courses' => $courses,
            'currentCourseId' => $courseId,
            'currentSearch' => $search,
        ]);
    }
}
