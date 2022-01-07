<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MustHavePersonalData
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->personalData->street === null ||
            Auth::user()->personalData->zip === null ||
            Auth::user()->personalData->city === null ||
            Auth::user()->personalData->mobile_phone === null
        ) {
            return redirect(route('set-personal-data.form'));
        }

        return $next($request);
    }
}
