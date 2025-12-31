<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from header, query param, or default to 'id'
        $locale = $request->header('Accept-Language')
            ?? $request->query('lang')
            ?? 'id';

        // Validate locale
        $supportedLocales = ['id', 'en'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'id';
        }

        // Set application locale
        app()->setLocale($locale);

        return $next($request);
    }
}
