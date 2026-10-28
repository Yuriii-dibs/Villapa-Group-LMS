<?php

namespace App\Http\Requests;

use App\Models\ClassSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to create a session for this course.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $courseId = $this->input('course_id');

        return $courseId && $user->teachesCourse((int) $courseId);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'provider' => ['required', Rule::in([ClassSession::PROVIDER_JITSI, ClassSession::PROVIDER_EXTERNAL])],
            'meeting_url' => [
                Rule::requiredIf(fn() => $this->input('provider') === ClassSession::PROVIDER_EXTERNAL),
                'nullable',
                'url',
                'max:500',
            ],
        ];
    }
}
