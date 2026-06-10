<?php

namespace App\Http\Controllers\Collaborator;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Collaborator/Settings', [
            'notifications_enabled' => (bool) auth()->user()->notifications_enabled,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'notifications_enabled' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'notifications_enabled' => $request->boolean('notifications_enabled'),
        ]);

        return redirect()->route('collaborator.settings')
            ->with('success', 'Preferências salvas com sucesso.');
    }
}
