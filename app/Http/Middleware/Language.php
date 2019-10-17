<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Language
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale', config('app.fallback_locale'));
        app()->setLocale($locale);

        return $next($request);
    }
}
