<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');

        // Administrators cannot suspend their own account
        if ($targetUser && $targetUser->id === $this->user()->id && $this->input('status') === User::STATUS_SUSPENDED) {
            return false;
        }

        return $this->user()->can('updateStatus', $targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED, User::STATUS_PENDING])],
        ];
    }
}
