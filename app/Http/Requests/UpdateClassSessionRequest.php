<?php

namespace App\Http\Requests;

use App\Models\ClassSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to update this session.
     */
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session && $this->user()->can('manage', $session);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
