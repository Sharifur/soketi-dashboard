<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySoketiToken
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() !== config('soketi.token')) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
