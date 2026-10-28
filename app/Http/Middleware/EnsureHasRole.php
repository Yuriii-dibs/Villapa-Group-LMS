<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED)
                : redirect()->guest(route('login'));
        }

        if ($user->isSuspended()) {
            abort(Response::HTTP_FORBIDDEN, 'Your account has been suspended.');
        }

        // Check if user has at least one of the specified roles
        if (! empty($roles) && ! $user->hasRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized access to this portal.');
        }

        return $next($request);
    }
}
