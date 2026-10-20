<x-filament-panels::page>
    <div class="lubricar-catalog-layout">
        <div class="lubricar-catalog">
            <div class="lubricar-catalog-search">
                <input
                    type="search"
                    wire:model.live.debounce.400ms="q"
                    placeholder="Buscar por nombre, marca, viscosidad…"
                    class="lubricar-search-input"
                />
            </div>

            @if ($error)
                <div class="lubricar-empty">
                    <p class="lubricar-empty-title">No se pudo cargar el catálogo</p>
                    <p class="lubricar-empty-text">{{ $error }}</p>
                    <button type="button" wire:click="loadProductos" class="lubricar-empty-cta">
                        Reintentar
                    </button>
                </div>
            @elseif (count($productos) === 0)
                <div class="lubricar-empty">
                    <div class="lubricar-empty-icon">
                        <x-filament::icon icon="heroicon-o-cube" class="h-7 w-7" />
                    </div>
                    <p class="lubricar-empty-title">Sin productos</p>
                    <p class="lubricar-empty-text">
                        No hay productos publicados en ecommerce, o la búsqueda no dio resultados.
                    </p>
                </div>
            @else
                <p class="lubricar-catalog-count">{{ $total }} producto(s)</p>

                <div class="lubricar-catalog-grid" wire:loading.class="opacity-60">
                    @foreach ($productos as $p)
                        @php
                            $pid = (int) ($p['gestion_product_id'] ?? $p['id']);
                            $sinStock = (float) ($p['stock'] ?? 0) <= 0;
                        @endphp
                        <article class="lubricar-product-card">
                            <div class="lubricar-product-image">
                                @if (! empty($p['imagen_url']))
                                    <img src="{{ $p['imagen_url'] }}" alt="{{ $p['nombre'] }}" loading="lazy">
                                @else
                                    <div class="lubricar-product-image-placeholder">
                                        <x-filament::icon icon="heroicon-o-photo" class="h-8 w-8" />
                                    </div>
                                @endif
                            </div>

                            <div class="lubricar-product-body">
                                <div class="lubricar-product-brand">{{ $p['marca'] ?? '—' }}</div>
                                <h3 class="lubricar-product-name">{{ $p['nombre'] }}</h3>
                                <p class="lubricar-product-meta">{{ $p['detalle_presentacion'] ?? '' }}</p>
                                <div class="lubricar-product-footer">
                                    <div>
                                        <div class="lubricar-product-price">{{ $this->formatPrice($p['precio'] ?? 0) }}</div>
                                        <div class="lubricar-product-stock">
                                            Stock: {{ $this->formatStock($p['stock'] ?? 0) }}
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="lubricar-add-btn"
                                        wire:click="addToCart({{ $pid }})"
                                        @disabled($sinStock)
                                    >
                                        {{ $sinStock ? 'Sin stock' : 'Agregar' }}
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($lastPage > 1)
                    <div class="lubricar-catalog-pager">
                        <button type="button" wire:click="prevPage" @disabled($currentPage <= 1) class="lubricar-pager-btn">
                            Anterior
                        </button>
                        <span>Página {{ $currentPage }} de {{ $lastPage }}</span>
                        <button type="button" wire:click="nextPage" @disabled($currentPage >= $lastPage) class="lubricar-pager-btn">
                            Siguiente
                        </button>
                    </div>
                @endif
            @endif
        </div>

        {{-- Pedido en armado (lateral) --}}
        <aside class="lubricar-cart">
            <div class="lubricar-cart-head">
                <div>
                    <h3 class="lubricar-cart-title">
                        {{ $this->editingPedidoId ? 'Editando pedido' : 'Tu pedido' }}
                    </h3>
                    <p class="lubricar-cart-sub">
                        {{ $this->cartCount }} ítem(s) · {{ $this->formatPrice($this->cartSubtotal) }}
                    </p>
                </div>
            </div>

            @if (count($this->cartItems) === 0)
                <div class="lubricar-cart-empty">
                    Andá agregando productos. Vas a verlos acá al instante.
                </div>
            @else
                <div class="lubricar-cart-items">
                    @foreach ($this->cartItems as $item)
                        <div class="lubricar-cart-item" wire:key="cart-{{ $item['producto_id'] }}">
                            <div class="lubricar-cart-item-info">
                                <div class="lubricar-cart-item-name">{{ $item['nombre'] }}</div>
                                <div class="lubricar-cart-item-meta">
                                    {{ $item['marca'] }} · {{ $this->formatPrice($item['precio_unitario']) }}
                                </div>
                            </div>
                            <div class="lubricar-cart-item-actions">
                                <input
                                    type="number"
                                    min="1"
                                    step="1"
                                    value="{{ $item['cantidad'] }}"
                                    class="lubricar-cart-qty"
                                    wire:change="updateCartQty({{ $item['producto_id'] }}, $event.target.value)"
                                />
                                <button type="button" class="lubricar-cart-remove" wire:click="removeFromCart({{ $item['producto_id'] }})">
                                    <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <label class="lubricar-cart-obs">
                    <span>Observaciones</span>
                    <textarea
                        wire:model.blur="observaciones"
                        rows="2"
                        placeholder="Ej: entregar el jueves"
                    ></textarea>
                </label>

                <div class="lubricar-cart-total">
                    <span>Total</span>
                    <strong>{{ $this->formatPrice($this->cartSubtotal) }}</strong>
                </div>

                <button
                    type="button"
                    class="lubricar-cart-close-btn"
                    wire:click="cerrarPedido"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="cerrarPedido">
                        {{ $this->editingPedidoId ? 'Actualizar pedido' : 'Cerrar pedido' }}
                    </span>
                    <span wire:loading wire:target="cerrarPedido">Enviando…</span>
                </button>

                <button type="button" class="lubricar-cart-clear" wire:click="clearCart">
                    Vaciar
                </button>
            @endif
        </aside>
    </div>
</x-filament-panels::page>
