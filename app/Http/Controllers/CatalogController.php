<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the public landing page with headline and featured academic programs.
     */
    public function home(): View
    {
        $programs = Course::query()
            ->whereIn('slug', [
                'bs-information-technology',
                'bs-business-administration',
                'bs-hospitality-management',
                'bachelor-of-secondary-education',
                'bs-criminology',
                'bs-midwifery',
            ])
            ->where('approval_status', Course::APPROVAL_APPROVED)
            ->where('publication_status', Course::PUB_PUBLISHED)
            ->with(['category', 'instructor'])
            ->orderBy('id')
            ->get();

        return view('landing', [
            'programs' => $programs,
        ]);
    }

    /**
     * Display the public catalog of published courses.
     * Students only see courses within their assigned degree program.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Course::query()
            ->where('approval_status', Course::APPROVAL_APPROVED)
            ->where('publication_status', Course::PUB_PUBLISHED)
            ->with(['category', 'instructor'])
            ->withCount('lessons');

        // If the logged-in user is a student with a program, restrict to that program's courses
        if ($user && $user->isStudent() && $user->program_id) {
            $query->where('category_id', $user->program_id);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        $courses = $query->latest('published_at')->paginate(9)->withQueryString();

        // Students only see their own program's category as a filter option
        $categories = ($user && $user->isStudent() && $user->program_id)
            ? Category::where('id', $user->program_id)->get()
            : Category::has('courses')->orderBy('name')->get();

        return view('catalog.index', [
            'courses'           => $courses,
            'categories'        => $categories,
            'currentSearch'     => $request->input('search'),
            'currentCategory'   => $request->input('category'),
            'currentDifficulty' => $request->input('difficulty'),
            'studentProgram'    => ($user && $user->isStudent()) ? $user->load('program')->program : null,
        ]);
    }

    /**
     * Display single course overview in catalog.
     */
    public function show(Request $request, Course $course): View
    {
        if (! $course->isPublished() && ! $request->user()?->can('view', $course)) {
            abort(404);
        }

        $course->load(['category', 'instructor', 'modules.lessons']);

        $isEnrolled = false;
        $progress = 0;

        if ($user = $request->user()) {
            $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
            if ($enrollment) {
                $isEnrolled = true;
                $progress = $enrollment->progressPercentage();
            }
        }

        return view('catalog.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'progress' => $progress,
        ]);
    }
}
