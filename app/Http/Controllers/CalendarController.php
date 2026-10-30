<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\Role;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Display the combined interactive academic calendar and upcoming 7-day schedule.
     */
    public function index(Request $request, CalendarService $calendarService): View|JsonResponse
    {
        $user = $request->user();

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $monthItems = $calendarService->getMonthItems($user, $year, $month);
        $upcomingItems = $calendarService->getNextSevenDaysItems($user);

        if ($request->wantsJson()) {
            return response()->json([
                'month_items' => $monthItems,
                'upcoming_items' => $upcomingItems,
            ]);
        }

        // Available courses for instructor event creation
        $myCourses = $user->coursesAsInstructor()->orderBy('title')->get();
        $canCreateGeneral = $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN]);
        $canCreateCourseEvent = $user->hasRole(Role::INSTRUCTOR) && $myCourses->isNotEmpty();

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $itemsByDayJs = [];
        for ($d = 1; $d <= $currentDate->daysInMonth; $d++) {
            $itemsByDayJs[$d] = [];
        }
        foreach ($monthItems as $item) {
            if (! $item->starts_at) {
                continue;
            }
            $dayNum = (int) $item->starts_at->format('j');
            if (! isset($itemsByDayJs[$dayNum])) {
                continue;
            }
            $itemsByDayJs[$dayNum][] = [
                'id' => $item->id,
                'raw_id' => $item->raw_id,
                'title' => $item->title,
                'description' => $item->description,
                'type' => $item->type,
                'starts_at' => $item->starts_at->toIso8601String(),
                'ends_at' => $item->ends_at?->toIso8601String(),
                'course_title' => $item->course_title,
                'is_session' => (bool) $item->is_session,
                'session_url' => $item->session_url,
                'can_delete' => (bool) $item->can_delete,
            ];
        }

        $itemsByDayJsJson = json_encode(
            $itemsByDayJs,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES
        ) ?: '{}';

        return view('calendar.index', compact(
            'monthItems',
            'upcomingItems',
            'year',
            'month',
            'currentDate',
            'prevMonth',
            'nextMonth',
            'myCourses',
            'canCreateGeneral',
            'canCreateCourseEvent',
            'itemsByDayJsJson'
        ));
    }

    /**
     * Store a new calendar event (personal, course, or general).
     * Only admins and super-admins can create events.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            abort(403, 'Only administrators can add calendar events.');
        }

        $type = $request->validated('type');

        $courseId = in_array($type, [Event::TYPE_CLASS, Event::TYPE_EXAM])
            ? (int) $request->validated('course_id')
            : null;

        $userId = $type === Event::TYPE_PERSONAL ? $user->id : null;

        Event::create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'type' => $type,
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'course_id' => $courseId,
            'user_id' => $userId,
            'created_by' => $user->id,
        ]);

        return back()->with('status', 'Calendar event created successfully.');
    }

    /**
     * Delete an authorized calendar event.
     * Only admins and super-admins can delete events.
     */
    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            abort(403, 'Only administrators can delete calendar events.');
        }

        $event->delete();

        return back()->with('status', 'Calendar event removed.');
    }
}
