<?php

namespace App\Http\Middleware;

use Closure;

class SetupLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $logger = \Log::driver();
        $logger->pushProcessor(function ($record) {
            $record['extra']['pid'] = getmypid();
            return $record;
        });

        return $next($request);
    }
}
