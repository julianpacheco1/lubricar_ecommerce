<?php

namespace App\Filament\Pages;

use App\Services\Cart\CartService;
use App\Services\Gestion\EcommerceApi;
use App\Services\Gestion\GestionApiException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class Pedidos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.pages.pedidos';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $title = 'Pedidos';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    public array $pedidos = [];

    public ?array $detalle = null;

    public ?string $error = null;

    public string $filtroEstado = '';

    public function mount(): void
    {
        $this->loadPedidos();
    }

    public function getSubheading(): ?string
    {
        return 'Pendientes se pueden editar o cancelar. Aceptados quedan solo lectura.';
    }

    public function updatedFiltroEstado(): void
    {
        $this->detalle = null;
        $this->loadPedidos();
    }

    public function loadPedidos(): void
    {
        $this->error = null;

        if (! session('gestion_token')) {
            $this->pedidos = [];
            $this->error = 'No hay sesión con GESTIÓN.';

            return;
        }

        try {
            $query = ['per_page' => 50];
            if (filled($this->filtroEstado)) {
                $query['estado'] = $this->filtroEstado;
            }

            $response = app(EcommerceApi::class)->pedidos($query);
            $this->pedidos = $response['data'] ?? (is_array($response) ? $response : []);
        } catch (\Throwable $e) {
            Log::warning('Error listando pedidos', ['e' => $e->getMessage()]);
            $this->pedidos = [];
            $this->error = 'No se pudieron cargar los pedidos.';
        }
    }

    public function verPedido(int $id): void
    {
        try {
            $this->detalle = app(EcommerceApi::class)->pedido($id);
        } catch (\Throwable $e) {
            Notification::make()->title('No se pudo abrir el pedido')->body($e->getMessage())->danger()->send();
        }
    }

    public function cerrarDetalle(): void
    {
        $this->detalle = null;
    }

    public function editarPedido(int $id): void
    {
        try {
            $pedido = app(EcommerceApi::class)->pedido($id);

            if (! ($pedido['editable'] ?? false)) {
                Notification::make()->title('Este pedido ya no se puede editar')->warning()->send();

                return;
            }

            app(CartService::class)->loadFromPedido($pedido);

            Notification::make()
                ->title('Pedido cargado para editar')
                ->body('Modificá ítems en el catálogo y tocá “Actualizar pedido”.')
                ->success()
                ->send();

            $this->redirect(Catalogo::getUrl(), navigate: true);
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function cancelarPedido(int $id): void
    {
        try {
            $response = app(EcommerceApi::class)->cancelarPedido($id);
            $this->detalle = null;
            $this->loadPedidos();

            Notification::make()
                ->title('Pedido cancelado')
                ->body($response['message'] ?? 'Stock devuelto en GESTIÓN.')
                ->success()
                ->send();
        } catch (GestionApiException $e) {
            Notification::make()->title('No se pudo cancelar')->body($e->getMessage())->danger()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function formatPrice(mixed $precio): string
    {
        return '$ '.number_format((float) $precio, 2, ',', '.');
    }

    public function estadoColor(string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'pending',
            'aceptado' => 'ok',
            'cancelado' => 'cancel',
            default => 'cancel',
        };
    }
}
