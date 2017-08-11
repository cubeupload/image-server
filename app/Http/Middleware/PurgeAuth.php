<?php

namespace App\Http\Middleware;

use Closure;

class PurgeAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->has('filename'))
        {
            return response('File not supplied.', 400);
        }

        $filename = $request->input('filename');

        

        return $next($request);
    }
}
