<?php

namespace App\Services\Cart;

class CartService
{
    public const SESSION_KEY = 'pedido_borrador';

    public function all(): array
    {
        return session(self::SESSION_KEY, [
            'pedido_id' => null,
            'observaciones' => '',
            'items' => [],
        ]);
    }

    public function items(): array
    {
        return array_values($this->all()['items'] ?? []);
    }

    public function count(): int
    {
        return collect($this->items())->sum('cantidad');
    }

    public function subtotal(): float
    {
        return (float) collect($this->items())->sum(
            fn (array $item) => (float) $item['precio_unitario'] * (float) $item['cantidad']
        );
    }

    public function editingPedidoId(): ?int
    {
        $id = $this->all()['pedido_id'] ?? null;

        return $id ? (int) $id : null;
    }

    public function observaciones(): string
    {
        return (string) ($this->all()['observaciones'] ?? '');
    }

    public function setObservaciones(string $observaciones): void
    {
        $cart = $this->all();
        $cart['observaciones'] = $observaciones;
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * @param  array{producto_id:int,nombre:string,marca?:string,detalle?:string,precio_unitario:float,stock?:float,imagen_url?:?string}  $product
     */
    public function add(array $product, float $cantidad = 1): void
    {
        $cart = $this->all();
        $id = (int) $product['producto_id'];
        $items = $cart['items'];

        if (isset($items[$id])) {
            $items[$id]['cantidad'] = (float) $items[$id]['cantidad'] + $cantidad;
        } else {
            $items[$id] = [
                'producto_id' => $id,
                'nombre' => $product['nombre'],
                'marca' => $product['marca'] ?? '',
                'detalle' => $product['detalle'] ?? '',
                'precio_unitario' => (float) $product['precio_unitario'],
                'stock' => (float) ($product['stock'] ?? 0),
                'imagen_url' => $product['imagen_url'] ?? null,
                'cantidad' => $cantidad,
            ];
        }

        $stock = (float) ($items[$id]['stock'] ?? 0);
        if ($stock > 0 && $items[$id]['cantidad'] > $stock) {
            $items[$id]['cantidad'] = $stock;
        }

        if ($items[$id]['cantidad'] <= 0) {
            unset($items[$id]);
        }

        $cart['items'] = $items;
        session([self::SESSION_KEY => $cart]);
    }

    public function setCantidad(int $productoId, float $cantidad): void
    {
        $cart = $this->all();
        $items = $cart['items'];

        if (! isset($items[$productoId])) {
            return;
        }

        $stock = (float) ($items[$productoId]['stock'] ?? 0);
        if ($cantidad <= 0) {
            unset($items[$productoId]);
        } else {
            $items[$productoId]['cantidad'] = $stock > 0 ? min($cantidad, $stock) : $cantidad;
        }

        $cart['items'] = $items;
        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $productoId): void
    {
        $cart = $this->all();
        unset($cart['items'][$productoId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Carga un pedido pendiente de GESTIÓN en el borrador local para editarlo.
     */
    public function loadFromPedido(array $pedido): void
    {
        $items = [];

        foreach ($pedido['items'] ?? [] as $item) {
            $id = (int) $item['producto_id'];
            $items[$id] = [
                'producto_id' => $id,
                'nombre' => $item['nombre'] ?? 'Producto',
                'marca' => $item['marca'] ?? '',
                'detalle' => $item['detalle_presentacion'] ?? '',
                'precio_unitario' => (float) ($item['precio_unitario'] ?? 0),
                'stock' => 999999,
                'imagen_url' => null,
                'cantidad' => (float) ($item['cantidad'] ?? 1),
            ];
        }

        session([self::SESSION_KEY => [
            'pedido_id' => (int) $pedido['id'],
            'observaciones' => (string) ($pedido['observaciones'] ?? ''),
            'items' => $items,
        ]]);
    }

    /**
     * Payload para POST/PUT pedidos.
     *
     * @return array{observaciones:?string,items:array<int,array{producto_id:int,cantidad:float,precio_unitario:float}>}
     */
    public function toApiPayload(): array
    {
        return [
            'observaciones' => filled($this->observaciones()) ? $this->observaciones() : null,
            'items' => collect($this->items())
                ->map(fn (array $item) => [
                    'producto_id' => (int) $item['producto_id'],
                    'cantidad' => (float) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                ])
                ->values()
                ->all(),
        ];
    }
}
