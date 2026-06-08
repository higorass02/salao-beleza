<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;

class HandleInertiaRequests extends Middleware
{
    public function rootView(Request $request)
    {
        return 'app';
    }

    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'       => $request->user()->id,
                    'name'     => $request->user()->name,
                    'is_admin' => (bool) $request->user()->is_admin,
                ] : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }
}
