<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            $lastActivity = Session::get('lastActivity');

            if (!$lastActivity) {
                Session::put('lastActivity', time());
            } else {

                $timeout = config('session.lifetime') * 60;
                if (time() - $lastActivity > $timeout) {

                    Auth::logout();
                    Session::flush();
                    return redirect('/login')->with('message', 'Session expired due to inactivity.');
                }
            }

            Session::put('lastActivity', time());
        }

        return $next($request);
    }
}
