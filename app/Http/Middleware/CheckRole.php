<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            abort(403);
        }

        if ($user->role->name !== $role) {
            abort(403);
        }

        return $next($request);
    }
}