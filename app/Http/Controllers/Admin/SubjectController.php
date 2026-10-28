<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display the catalog of academic subjects.
     */
    public function index(Request $request): View
    {
        $subjects = Subject::withCount(['offerings', 'assignments'])
            ->orderBy('year_level')
            ->orderBy('subject_code')
            ->get();

        $instructors = User::whereHas('roles', fn($q) => $q->where('name', Role::INSTRUCTOR))
            ->orderBy('name')
            ->get();

        return view('admin.subjects.index', compact('subjects', 'instructors'));
    }

    /**
     * Store a new subject in the catalog.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_code' => ['required', 'string', 'max:50', 'unique:subjects,subject_code'],
            'title'        => ['required', 'string', 'max:255'],
            'units'        => ['required', 'integer', 'between:1,10'],
            'year_level'   => ['required', 'integer', 'between:1,5'],
            'program'      => ['required', 'string', 'max:50'],
            'block'        => ['nullable', 'string', 'max:10'],
            'section'      => ['nullable', 'string', 'max:50'],
            'instructor'   => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
        ]);

        $subject = Subject::create($validated);

        return back()->with('status', "Subject {$subject->subject_code} added to catalog.");
    }

    /**
     * Update an existing subject.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'subject_code' => ['required', 'string', 'max:50', 'unique:subjects,subject_code,' . $subject->id],
            'title'        => ['required', 'string', 'max:255'],
            'units'        => ['required', 'integer', 'between:1,10'],
            'year_level'   => ['required', 'integer', 'between:1,5'],
            'program'      => ['required', 'string', 'max:50'],
            'block'        => ['nullable', 'string', 'max:10'],
            'section'      => ['nullable', 'string', 'max:50'],
            'instructor'   => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
        ]);

        $subject->update($validated);

        // Keep existing subject offerings in sync with the subject's assigned instructor
        if (!empty($validated['instructor'])) {
            $instUser = User::whereHas('roles', fn($q) => $q->where('name', Role::INSTRUCTOR))
                ->where(function ($q) use ($validated) {
                    $q->where('name', $validated['instructor'])
                      ->orWhere('name', 'like', '%' . $validated['instructor'] . '%');
                })
                ->first();

            $subject->offerings()->update([
                'instructor_id'   => $instUser?->id,
                'instructor_name' => $instUser?->name ?: $validated['instructor'],
            ]);
        }

        return back()->with('status', "Subject {$subject->subject_code} updated successfully.");
    }

    /**
     * Delete a subject from catalog.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $code = $subject->subject_code;
        $subject->delete();

        return back()->with('status', "Subject {$code} deleted.");
    }
}
