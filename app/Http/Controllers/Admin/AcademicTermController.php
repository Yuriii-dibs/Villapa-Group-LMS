<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    /**
     * Display a listing of academic terms.
     */
    public function index(Request $request): View
    {
        $terms = AcademicTerm::withCount('offerings')
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();

        return view('admin.academic-terms.index', compact('terms'));
    }

    /**
     * Store a newly created academic term.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:50'],
            'semester'      => ['required', 'string', 'max:50'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after:start_date'],
            'is_current'    => ['nullable', 'boolean'],
        ]);

        $isCurrent = $request->boolean('is_current');

        if ($isCurrent) {
            AcademicTerm::where('is_current', true)->update(['is_current' => false]);
        }

        $term = AcademicTerm::create([
            'name'          => $validated['name'],
            'academic_year' => $validated['academic_year'],
            'semester'      => $validated['semester'],
            'start_date'    => $validated['start_date'],
            'end_date'      => $validated['end_date'],
            'is_current'    => $isCurrent,
        ]);

        return back()->with('status', "Academic Term {$term->name} created successfully.");
    }

    /**
     * Update an academic term.
     */
    public function update(Request $request, AcademicTerm $term): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:50'],
            'semester'      => ['required', 'string', 'max:50'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after:start_date'],
        ]);

        $term->update($validated);

        return back()->with('status', "Academic Term {$term->name} updated successfully.");
    }

    /**
     * Mark an academic term as current.
     */
    public function setCurrent(AcademicTerm $term): RedirectResponse
    {
        AcademicTerm::where('is_current', true)->update(['is_current' => false]);
        $term->update(['is_current' => true]);

        return back()->with('status', "{$term->name} marked as the active academic term.");
    }

    /**
     * Delete an academic term.
     */
    public function destroy(AcademicTerm $term): RedirectResponse
    {
        $name = $term->name;
        $term->delete();

        return back()->with('status', "Academic Term {$name} deleted.");
    }
}
