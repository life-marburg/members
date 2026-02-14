<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIfActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->status !== User::STATUS_UNLOCKED) {
            return redirect(route('not-yet-active'));
        }

        return $next($request);
    }
}
