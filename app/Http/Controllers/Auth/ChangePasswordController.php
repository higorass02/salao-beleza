<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Auth/ChangePassword', [
            'mustChange' => (bool) auth()->user()->must_change_password,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.confirmed'                => 'A confirmação de senha não confere.',
            'password.min'                      => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        $request->user()->update([
            'password'             => $request->password,
            'must_change_password' => false,
        ]);

        $destination = $request->user()->isCollaborator()
            ? route('collaborator.dashboard')
            : route('dashboard');

        return redirect($destination)->with('success', 'Senha alterada com sucesso.');
    }
}
