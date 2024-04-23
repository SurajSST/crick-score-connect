<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user has admin role
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Redirect or abort the request if the user is not an admin
        return abort(403, 'Unauthorized.');
    }
}
