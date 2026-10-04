<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isDeveloper() || $user->isApproved()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Akun belum disetujui.');
        }

        if ($request->routeIs('approval.pending')) {
            return $next($request);
        }

        return redirect()->route('approval.pending');
    }
}
