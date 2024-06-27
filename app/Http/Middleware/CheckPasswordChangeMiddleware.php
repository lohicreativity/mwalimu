<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckPasswordChangeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $datediff = Carbon::today()->diffInDays(Auth::user()->updated_at);

        if(Auth::user()->must_update_password == 1 || $datediff > 90){
            return redirect()->to('staff-change-password')->with('error','You must change your password');
        }

        return $next($request);
    }
}
