<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsCollaborator
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->isCollaborator()) {
            abort(403, 'Acesso exclusivo para colaboradores.');
        }

        return $next($request);
    }
}
