<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'settings' => Setting::allAsArray(),
            'services' => Service::orderBy('name')->get([
                'id', 'name', 'price', 'active', 'include_house_fee',
            ]),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_hours_start' => ['required', 'date_format:H:i'],
            'business_hours_end'   => ['required', 'date_format:H:i', 'after:business_hours_start'],
            'house_fee_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Configurações salvas com sucesso!');
    }
}
