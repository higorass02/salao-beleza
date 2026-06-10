<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsCollaborator
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user?->isCollaborator()) {
            abort(403, 'Acesso exclusivo para colaboradores.');
        }

        // Funcionário vinculado foi desativado — encerra a sessão imediatamente
        if ($user->employee?->active === false) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Seu acesso foi desativado pelo administrador.']);
        }

        return $next($request);
    }
}
