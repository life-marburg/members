<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MustHaveInstrument
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->instrumentGroups->count() === 0) {
            return redirect(route('set-instrument.form'));
        }

        return $next($request);
    }
}
