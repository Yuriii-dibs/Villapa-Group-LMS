<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');

        return $this->user()->can('updateRole', $targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in([Role::STUDENT, Role::INSTRUCTOR, Role::ADMIN])],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'role.in' => 'The selected role is invalid for this institution.',
        ];
    }
}
