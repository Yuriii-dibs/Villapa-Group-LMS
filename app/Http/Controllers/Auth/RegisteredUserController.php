<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Public registration strictly creates a Student with active status.
     * Privileged roles can never be requested or assigned via registration.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status'   => User::STATUS_PENDING, // Awaiting admin approval
            ]);

            $studentRole = Role::firstOrCreate(
                ['name' => Role::STUDENT],
                [
                    'display_name' => 'Student',
                    'description' => 'Course enrollment, content consumption, and learning activities.',
                ]
            );

            $user->assignRole($studentRole);

            return $user;
        });

        event(new Registered($user));

        // Do NOT log in — redirect to a pending-approval notice instead.
        return redirect()->route('register.pending');
    }
}
