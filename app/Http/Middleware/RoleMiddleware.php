<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (! $request->user()) {
            throw new AuthorizationException(__('messages.auth.unauthorized'));
        }

        // Check if user has required role
        if (! in_array($request->user()->role, $roles)) {
            throw new AuthorizationException(__('messages.auth.forbidden'));
        }
        
        return $next($request);
    }
}
