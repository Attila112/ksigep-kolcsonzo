<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! $request->user() ||
            $request->user()->role !== 'ADMIN'
        ) {
            abort(403, 'Nincs jogosultságod ehhez a művelethez.');
        }

        return $next($request);
    }
}