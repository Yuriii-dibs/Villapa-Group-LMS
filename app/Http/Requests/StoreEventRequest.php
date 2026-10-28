<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to create this event.
     * Only admins and super-admins can create calendar events.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type'        => ['required', 'string', Rule::in([Event::TYPE_CLASS, Event::TYPE_EXAM, Event::TYPE_PERSONAL, Event::TYPE_GENERAL])],
            'starts_at'   => ['required', 'date', 'after:today'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'course_id'   => [
                Rule::requiredIf(fn() => in_array($this->input('type'), [Event::TYPE_CLASS, Event::TYPE_EXAM])),
                'nullable',
                'exists:courses,id',
            ],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'starts_at.after' => 'The event start date must be a future date (tomorrow or later). Past dates and today are not allowed.',
            'ends_at.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
