<?php

namespace App\Http\Controllers\Auth\Client;

use App\Http\Controllers\Controller;
use App\Services\ClientService;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(private ClientService $clientService) {}

    public function redirect()
    {
        if (request()->filled(['service_id', 'employee_id', 'starts_at'])) {
            session(['booking_intent' => request()->only(['service_id', 'employee_id', 'starts_at'])]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $client = $this->clientService->findOrCreateByGoogle([
            'google_id'    => $googleUser->getId(),
            'name'         => $googleUser->getName(),
            'email'        => $googleUser->getEmail(),
            'google_avatar' => $googleUser->getAvatar(),
        ]);

        session(['booking_client' => [
            'client_id'    => $client->id,
            'google_id'    => $client->google_id,
            'name'         => $client->name,
            'email'        => $client->email,
            'google_avatar' => $client->google_avatar,
        ]]);

        return redirect()->route('booking.confirm');
    }
}
