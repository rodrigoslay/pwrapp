<?php

// app/Http/Middleware/DarkModeMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class DarkModeMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->dark_mode) {
            \Config::set('adminlte.layout_dark_mode', true);
        }

        return $next($request);
    }
}
