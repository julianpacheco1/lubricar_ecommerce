<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Gestion\EcommerceApi;
use App\Services\Gestion\GestionAuthException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        try {
            $payload = app(EcommerceApi::class)->login(
                email: $data['email'],
                password: $data['password'],
                deviceName: 'portal-web',
            );
        } catch (GestionAuthException $e) {
            throw ValidationException::withMessages([
                'data.email' => $e->message ?: 'Email o contraseña incorrectos.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'data.email' => 'No se pudo conectar con GESTIÓN. ¿Está levantada en :8000?',
            ]);
        }

        $cliente = $payload['cliente'] ?? [];
        $email = $cliente['email'] ?? $data['email'];
        $name = $cliente['razon_social']
            ?? $cliente['nombre_fantasia']
            ?? $email;

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(str()->random(32)),
                'role' => UserRole::Cliente,
                'is_active' => true,
            ],
        );

        if ($user instanceof FilamentUser && (! $user->canAccessPanel(Filament::getCurrentPanel()))) {
            throw ValidationException::withMessages([
                'data.email' => 'Este usuario no puede acceder al portal.',
            ]);
        }

        Filament::auth()->login($user, $data['remember'] ?? false);
        session()->regenerate();

        session([
            'gestion_token' => $payload['token'],
            'gestion_cliente' => $cliente,
            'gestion_cliente_id' => $cliente['id'] ?? null,
        ]);

        return app(LoginResponse::class);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Contraseña')
            ->password()
            ->required();
    }
}
