<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Catalogo;
use App\Filament\Pages\Cuenta;
use App\Filament\Pages\Pedidos;
use App\Services\Gestion\EcommerceApi;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;

class PanelOverview extends Widget
{
    protected static string $view = 'filament.widgets.panel-overview';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function getGreeting(): string
    {
        $cliente = session('gestion_cliente');
        $name = $cliente['razon_social']
            ?? $cliente['nombre_fantasia']
            ?? auth()->user()?->name
            ?? 'Cliente';

        return 'Hola, '.$name;
    }

    public function getSummaryCards(): array
    {
        $saldo = 0.0;

        if (session('gestion_token')) {
            try {
                $me = app(EcommerceApi::class)->me();
                $saldo = (float) ($me['saldo'] ?? 0);
            } catch (\Throwable $e) {
                Log::debug('Panel: no se pudo leer saldo GESTIÓN', ['e' => $e->getMessage()]);
            }
        }

        return [
            [
                'title' => 'Saldo actual',
                'value' => '$ '.number_format($saldo, 2, ',', '.'),
                'meta' => $saldo > 0 ? 'Pendiente de pago' : 'Sin deuda registrada',
                'link_label' => 'Ver cuenta corriente',
                'url' => Cuenta::getUrl(),
                'icon' => 'heroicon-o-banknotes',
                'tone' => $saldo > 0 ? 'warn' : 'ok',
            ],
            [
                'title' => 'Pedidos pendientes',
                'value' => '—',
                'meta' => 'Próximo paso: listar desde la API',
                'link_label' => 'Ver mis pedidos',
                'url' => Pedidos::getUrl(),
                'icon' => 'heroicon-o-clipboard-document-list',
                'tone' => 'default',
            ],
            [
                'title' => 'Pedidos este mes',
                'value' => '—',
                'meta' => 'Se completa con GET /pedidos',
                'link_label' => 'Ir a pedidos',
                'url' => Pedidos::getUrl(),
                'icon' => 'heroicon-o-shopping-bag',
                'tone' => 'default',
            ],
            [
                'title' => 'Catálogo',
                'value' => 'Explorar',
                'meta' => 'Productos con precio y stock desde GESTIÓN',
                'link_label' => 'Ver catálogo',
                'url' => Catalogo::getUrl(),
                'icon' => 'heroicon-o-cube',
                'tone' => 'default',
            ],
        ];
    }

    public function getRecentOrders(): array
    {
        return [];
    }
}
