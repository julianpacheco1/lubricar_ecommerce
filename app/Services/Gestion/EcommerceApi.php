<?php

namespace App\Services\Gestion;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EcommerceApi
{
    public function login(string $email, string $password, string $deviceName = 'web'): array
    {
        $response = $this->base()
            ->post('/auth/login', [
                'email' => $email,
                'password' => $password,
                'device_name' => $deviceName,
            ]);

        if ($response->status() === 422) {
            throw new GestionAuthException(
                $response->json('message') ?? 'Credenciales inválidas.',
                $response->json('errors') ?? [],
            );
        }

        $response->throw();

        return $response->json();
    }

    public function me(?string $token = null): array
    {
        return $this->auth($token)->get('/auth/me')->throw()->json();
    }

    public function logout(?string $token = null): void
    {
        try {
            $this->auth($token)->post('/auth/logout')->throw();
        } catch (RequestException) {
            // Token ya inválido: igual limpiamos sesión local.
        }
    }

    public function productos(array $query = [], ?string $token = null): array
    {
        return $this->auth($token)
            ->get('/productos', $query)
            ->throw()
            ->json();
    }

    public function producto(int $id, ?string $token = null): array
    {
        return $this->auth($token)
            ->get('/productos/'.$id)
            ->throw()
            ->json();
    }

    public function pedidos(array $query = [], ?string $token = null): array
    {
        return $this->auth($token)
            ->get('/pedidos', $query)
            ->throw()
            ->json();
    }

    public function pedido(int $id, ?string $token = null): array
    {
        $json = $this->auth($token)
            ->get('/pedidos/'.$id)
            ->throw()
            ->json();

        return $json['data'] ?? $json;
    }

    public function crearPedido(array $payload, ?string $token = null): array
    {
        $response = $this->auth($token)->post('/pedidos', $payload);

        if ($response->status() === 422) {
            throw new GestionApiException(
                $response->json('message') ?? 'No se pudo crear el pedido.',
                $response->json(),
            );
        }

        $response->throw();

        return $response->json();
    }

    public function actualizarPedido(int $id, array $payload, ?string $token = null): array
    {
        $response = $this->auth($token)->put('/pedidos/'.$id, $payload);

        if ($response->status() === 422) {
            throw new GestionApiException(
                $response->json('message') ?? 'No se pudo actualizar el pedido.',
                $response->json(),
            );
        }

        $response->throw();

        return $response->json();
    }

    public function cancelarPedido(int $id, ?string $token = null): array
    {
        $response = $this->auth($token)->post('/pedidos/'.$id.'/cancelar');

        if ($response->status() === 422) {
            throw new GestionApiException(
                $response->json('message') ?? 'No se pudo cancelar el pedido.',
                $response->json(),
            );
        }

        $response->throw();

        return $response->json();
    }

    protected function auth(?string $token = null): PendingRequest
    {
        $token ??= session('gestion_token');

        if (blank($token)) {
            throw new RuntimeException('No hay sesión con GESTIÓN. Volvé a iniciar sesión.');
        }

        return $this->base()->withToken($token);
    }

    protected function base(): PendingRequest
    {
        return Http::baseUrl(config('gestion.api_url'))
            ->acceptJson()
            ->asJson()
            ->timeout(config('gestion.timeout', 20));
    }
}
