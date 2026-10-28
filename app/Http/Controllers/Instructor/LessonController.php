<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function create(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load('modules');

        return view('instructor.lessons.create', [
            'course' => $course,
        ]);
    }

    public function store(StoreLessonRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validated();

        $moduleId = $validated['module_id'] ?? null;
        if (! $moduleId) {
            $defaultModule = $course->modules()->first() ?? Module::create([
                'course_id' => $course->id,
                'title' => 'Course Content',
                'order' => 1,
            ]);
            $moduleId = $defaultModule->id;
        }

        $order = $validated['order'] ?? ($course->lessons()->count() + 1);
        $slug = Str::slug($validated['title']) . '-' . Str::lower(Str::random(5));

        Lesson::create([
            'module_id' => $moduleId,
            'title' => $validated['title'],
            'slug' => $slug,
            'content_type' => $validated['content_type'],
            'body' => $validated['body'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'is_published' => true,
            'order' => $order,
        ]);

        return redirect()->route('instructor.courses.edit', $course)
            ->with('status', 'Lesson created successfully.');
    }

    public function edit(Lesson $lesson): View
    {
        $this->authorize('update', $lesson);

        $course = $lesson->module->course;

        return view('instructor.lessons.edit', [
            'lesson' => $lesson,
            'course' => $course,
        ]);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        $validated = $request->validated();

        $lesson->update([
            'title' => $validated['title'],
            'content_type' => $validated['content_type'],
            'body' => $validated['body'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'order' => $validated['order'] ?? $lesson->order,
        ]);

        return redirect()->route('instructor.courses.edit', $lesson->module->course)
            ->with('status', 'Lesson updated successfully.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $course = $lesson->module->course;
        $lesson->delete();

        return redirect()->route('instructor.courses.edit', $course)
            ->with('status', 'Lesson removed successfully.');
    }

    /**
     * Move a lesson up within its module order.
     */
    public function moveUp(Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        $module = $lesson->module;
        $lessons = $module->lessons()->orderBy('order')->orderBy('id')->get();
        $index = $lessons->search(fn ($item) => $item->id === $lesson->id);

        if ($index > 0) {
            $prev = $lessons[$index - 1];
            DB::transaction(function () use ($lesson, $prev, $module) {
                $temp = $lesson->order;
                $lesson->order = $prev->order;
                $prev->order = $temp;
                if ($lesson->order === $prev->order) {
                    $lesson->order = max(1, $prev->order - 1);
                }
                $lesson->save();
                $prev->save();

                // Re-normalize order 1..N
                $all = $module->lessons()->orderBy('order')->orderBy('id')->get();
                foreach ($all as $i => $item) {
                    $item->updateQuietly(['order' => $i + 1]);
                }
            });
        }

        return back()->with('status', "Lesson [{$lesson->title}] moved up.");
    }

    /**
     * Move a lesson down within its module order.
     */
    public function moveDown(Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        $module = $lesson->module;
        $lessons = $module->lessons()->orderBy('order')->orderBy('id')->get();
        $index = $lessons->search(fn ($item) => $item->id === $lesson->id);

        if ($index !== false && $index < $lessons->count() - 1) {
            $next = $lessons[$index + 1];
            DB::transaction(function () use ($lesson, $next, $module) {
                $temp = $lesson->order;
                $lesson->order = $next->order;
                $next->order = $temp;
                if ($lesson->order === $next->order) {
                    $next->order = $lesson->order + 1;
                }
                $lesson->save();
                $next->save();

                // Re-normalize order 1..N
                $all = $module->lessons()->orderBy('order')->orderBy('id')->get();
                foreach ($all as $i => $item) {
                    $item->updateQuietly(['order' => $i + 1]);
                }
            });
        }

        return back()->with('status', "Lesson [{$lesson->title}] moved down.");
    }
}
