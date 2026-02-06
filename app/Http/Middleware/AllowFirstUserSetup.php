<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AllowFirstUserSetup
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        if (!User::query()->exists()) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
