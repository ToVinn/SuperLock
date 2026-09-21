<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        abort_unless($user, 401, 'Login diperlukan');
        abort_if($roles !== [] && ! in_array($user->role, $roles, true), 403, 'Akses ditolak untuk peran Anda.');

        return $next($request);
    }
}
