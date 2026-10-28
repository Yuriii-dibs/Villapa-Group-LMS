<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    private const PROGRAM_SLUGS = [
        'bs-information-technology',
        'bs-business-administration',
        'bs-hospitality-management',
        'bachelor-of-secondary-education',
        'bs-criminology',
        'bs-midwifery',
    ];

    /**
     * Display the public program catalog showing published academic degree programs.
     */
    public function index(Request $request): View
    {
        $query = Course::query()
            ->whereIn('slug', self::PROGRAM_SLUGS)
            ->where('approval_status', Course::APPROVAL_APPROVED)
            ->where('publication_status', Course::PUB_PUBLISHED)
            ->with(['category', 'instructor'])
            ->withCount('lessons');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('id')->paginate(9)->withQueryString();

        return view('programs.index', [
            'programs'      => $programs,
            'currentSearch' => $request->input('search'),
        ]);
    }

    /**
     * Display a single program's detail page (the subject detail).
     */
    public function show(Request $request, Course $course): View
    {
        if (! $course->isPublished() && ! $request->user()?->can('view', $course)) {
            abort(404);
        }

        $course->load(['category', 'instructor', 'modules.lessons']);

        $isEnrolled = false;
        $progress   = 0;

        if ($user = $request->user()) {
            $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
            if ($enrollment) {
                $isEnrolled = true;
                $progress   = $enrollment->progressPercentage();
            }
        }

        return view('programs.show', [
            'program'    => $course,
            'isEnrolled' => $isEnrolled,
            'progress'   => $progress,
        ]);
    }
}
