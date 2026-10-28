<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\SubjectOffering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferingController extends Controller
{
    /**
     * Display a list of class offerings taught by this instructor.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $offerings = SubjectOffering::with(['subject', 'section', 'academicTerm', 'students'])
            ->withCount('students')
            ->where(function ($q) use ($user) {
                $q->where('instructor_id', $user->id)
                    ->orWhere('instructor_name', 'like', '%' . $user->name . '%');
            })
            ->get();

        return view('instructor.offerings.index', compact('offerings', 'user'));
    }

    /**
     * Display a single class offering with enrolled student lists and Meet link settings.
     */
    public function show(Request $request, SubjectOffering $offering): View
    {
        $user = $request->user();

        // Ensure this instructor teaches this offering or is admin
        abort_unless(
            $offering->instructor_id === $user->id ||
            str_contains($offering->instructor_name ?? '', $user->name) ||
            $user->hasRole('admin'),
            403,
            'Unauthorized access to this offering.'
        );

        $offering->load(['subject.assignments.submissions', 'section', 'academicTerm', 'students']);

        return view('instructor.offerings.show', compact('offering', 'user'));
    }

    /**
     * Update the Google Meet link for this offering.
     */
    public function updateMeetLink(Request $request, SubjectOffering $offering): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $offering->instructor_id === $user->id ||
            str_contains($offering->instructor_name ?? '', $user->name) ||
            $user->hasRole('admin'),
            403
        );

        $validated = $request->validate([
            'meet_link' => ['nullable', 'url', 'max:255'],
        ]);

        $offering->meet_link = $validated['meet_link'];
        $offering->save();

        // Also sync to subject if needed
        if ($offering->subject && !empty($validated['meet_link'])) {
            $offering->subject->update(['meet_link' => $validated['meet_link']]);
        }

        return back()->with('status', 'Google Meet link updated successfully for ' . $offering->subject?->subject_code . '.');
    }
}
