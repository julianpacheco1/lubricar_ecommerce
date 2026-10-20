<?php

namespace App\Listeners;

use App\Services\Gestion\EcommerceApi;
use Illuminate\Auth\Events\Logout;

class LogoutGestionSession
{
    public function handle(Logout $event): void
    {
        $token = session('gestion_token');

        if ($token) {
            app(EcommerceApi::class)->logout($token);
        }

        session()->forget([
            'gestion_token',
            'gestion_cliente',
            'gestion_cliente_id',
        ]);
    }
}
