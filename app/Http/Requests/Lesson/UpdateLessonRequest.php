<?php

namespace App\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lesson = $this->route('lesson');

        return $lesson && $this->user()->can('update', $lesson);
    }

    public function rules(): array
    {
        return [
            'module_id' => ['nullable', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'in:text,video,pdf,file,external_link'],
            'body' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'is_preview' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
