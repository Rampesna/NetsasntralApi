<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenControl
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->appToken || $request->appToken != env('APP_TOKEN')) {
            return response()->json(null, 400);
        }

        return $next($request);
    }
}
