<?php

namespace App\Filament\Pages;

use App\Services\Cart\CartService;
use App\Services\Gestion\EcommerceApi;
use App\Services\Gestion\GestionApiException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;

class Catalogo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static string $view = 'filament.pages.catalogo';

    protected static ?string $navigationLabel = 'Catálogo';

    protected static ?string $title = 'Catálogo';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    #[Url]
    public string $q = '';

    public int $currentPage = 1;

    public array $productos = [];

    public int $total = 0;

    public int $lastPage = 1;

    public ?string $error = null;

    public string $observaciones = '';

    public bool $cerrando = false;

    public function mount(): void
    {
        $this->observaciones = app(CartService::class)->observaciones();
        $this->loadProductos();
    }

    public function updatedQ(): void
    {
        $this->currentPage = 1;
        $this->loadProductos();
    }

    public function updatedObservaciones(string $value): void
    {
        app(CartService::class)->setObservaciones($value);
    }

    public function getSubheading(): ?string
    {
        return 'Agregá productos al pedido. Cuando termines, usá “Cerrar pedido”.';
    }

    public function getCartItemsProperty(): array
    {
        return app(CartService::class)->items();
    }

    public function getCartCountProperty(): int
    {
        return app(CartService::class)->count();
    }

    public function getCartSubtotalProperty(): float
    {
        return app(CartService::class)->subtotal();
    }

    public function getEditingPedidoIdProperty(): ?int
    {
        return app(CartService::class)->editingPedidoId();
    }

    public function loadProductos(): void
    {
        $this->error = null;

        if (! session('gestion_token')) {
            $this->productos = [];
            $this->error = 'No hay sesión con GESTIÓN. Cerrá sesión e ingresá con tu usuario del portal.';

            return;
        }

        try {
            $query = [
                'per_page' => 12,
                'page' => $this->currentPage,
            ];

            if (filled($this->q)) {
                $query['q'] = $this->q;
            }

            $response = app(EcommerceApi::class)->productos($query);

            $this->productos = $response['data'] ?? [];
            $this->total = (int) ($response['total'] ?? 0);
            $this->lastPage = (int) ($response['last_page'] ?? 1);
            $this->currentPage = (int) ($response['current_page'] ?? 1);
        } catch (\Throwable $e) {
            Log::warning('Error cargando catálogo GESTIÓN', ['error' => $e->getMessage()]);
            $this->productos = [];
            $this->error = 'No se pudieron cargar los productos. Revisá que GESTIÓN esté en :8000.';

            Notification::make()
                ->title('Error al cargar catálogo')
                ->body($this->error)
                ->danger()
                ->send();
        }
    }

    public function addToCart(int $productoId): void
    {
        $product = collect($this->productos)->firstWhere('id', $productoId)
            ?? collect($this->productos)->firstWhere('gestion_product_id', $productoId);

        if (! $product) {
            try {
                $product = app(EcommerceApi::class)->producto($productoId);
                $product = $product['data'] ?? $product;
            } catch (\Throwable) {
                Notification::make()->title('Producto no encontrado')->danger()->send();

                return;
            }
        }

        $stock = (float) ($product['stock'] ?? 0);
        if ($stock <= 0) {
            Notification::make()->title('Sin stock')->body('Este producto no tiene stock disponible.')->warning()->send();

            return;
        }

        app(CartService::class)->add([
            'producto_id' => (int) ($product['gestion_product_id'] ?? $product['id']),
            'nombre' => $product['nombre'] ?? 'Producto',
            'marca' => $product['marca'] ?? '',
            'detalle' => $product['detalle_presentacion'] ?? '',
            'precio_unitario' => (float) ($product['precio'] ?? 0),
            'stock' => $stock,
            'imagen_url' => $product['imagen_url'] ?? null,
        ]);

        Notification::make()
            ->title('Agregado al pedido')
            ->success()
            ->send();
    }

    public function updateCartQty(int $productoId, $cantidad): void
    {
        app(CartService::class)->setCantidad($productoId, (float) $cantidad);
    }

    public function removeFromCart(int $productoId): void
    {
        app(CartService::class)->remove($productoId);
    }

    public function clearCart(): void
    {
        app(CartService::class)->clear();
        $this->observaciones = '';

        Notification::make()->title('Pedido borrador vaciado')->success()->send();
    }

    public function cerrarPedido(): void
    {
        $cart = app(CartService::class);

        if (count($cart->items()) === 0) {
            Notification::make()->title('El pedido está vacío')->warning()->send();

            return;
        }

        $this->cerrando = true;
        $cart->setObservaciones($this->observaciones);

        try {
            $payload = $cart->toApiPayload();
            $editingId = $cart->editingPedidoId();

            if ($editingId) {
                $response = app(EcommerceApi::class)->actualizarPedido($editingId, $payload);
                $message = $response['message'] ?? 'Pedido actualizado en GESTIÓN.';
            } else {
                $response = app(EcommerceApi::class)->crearPedido($payload);
                $message = $response['message'] ?? 'Pedido enviado a GESTIÓN.';
            }

            $cart->clear();
            $this->observaciones = '';
            $this->loadProductos();

            Notification::make()
                ->title($editingId ? 'Pedido actualizado' : 'Pedido cerrado')
                ->body($message)
                ->success()
                ->send();

            $this->redirect(Pedidos::getUrl(), navigate: true);
        } catch (GestionApiException $e) {
            Notification::make()
                ->title('No se pudo cerrar el pedido')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error cerrando pedido', ['e' => $e->getMessage()]);
            Notification::make()
                ->title('Error de conexión con GESTIÓN')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->cerrando = false;
        }
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->lastPage) {
            $this->currentPage++;
            $this->loadProductos();
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadProductos();
        }
    }

    public function formatPrice(mixed $precio): string
    {
        return '$ '.number_format((float) $precio, 2, ',', '.');
    }

    public function formatStock(mixed $stock): string
    {
        $n = (float) $stock;

        return fmod($n, 1.0) === 0.0
            ? (string) (int) $n
            : number_format($n, 1, ',', '.');
    }
}
