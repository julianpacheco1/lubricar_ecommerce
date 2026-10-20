<x-filament-panels::page>
    <div class="lubricar-pedidos">
        <div class="lubricar-pedidos-toolbar">
            <select wire:model.live="filtroEstado" class="lubricar-search-input" style="max-width: 16rem;">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendientes</option>
                <option value="aceptado">Aceptados</option>
                <option value="cancelado">Cancelados</option>
            </select>
            <button type="button" class="lubricar-pager-btn" wire:click="loadPedidos">Actualizar</button>
        </div>

        @if ($error)
            <div class="lubricar-empty">
                <p class="lubricar-empty-title">Error</p>
                <p class="lubricar-empty-text">{{ $error }}</p>
            </div>
        @elseif (count($pedidos) === 0)
            <div class="lubricar-empty">
                <div class="lubricar-empty-icon">
                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-7 w-7" />
                </div>
                <p class="lubricar-empty-title">Todavía no tenés pedidos</p>
                <p class="lubricar-empty-text">Armá uno desde el catálogo y cerralo para enviarlo a LubriCar.</p>
                <a href="{{ \App\Filament\Pages\Catalogo::getUrl() }}" class="lubricar-empty-cta">Ir al catálogo</a>
            </div>
        @else
            <div class="lubricar-orders">
                @foreach ($pedidos as $pedido)
                    <button
                        type="button"
                        class="lubricar-order-row lubricar-order-row--btn"
                        wire:click="verPedido({{ $pedido['id'] }})"
                    >
                        <div>
                            <div class="lubricar-order-num">#{{ $pedido['numero'] ?? $pedido['id'] }}</div>
                            <div class="lubricar-order-date">{{ \Illuminate\Support\Carbon::parse($pedido['created_at'] ?? now())->format('d/m/Y H:i') }}</div>
                        </div>
                        <span @class(['lubricar-badge', 'lubricar-badge--'.$this->estadoColor($pedido['estado'] ?? '')])>
                            {{ $pedido['estado_label'] ?? ucfirst($pedido['estado'] ?? '') }}
                        </span>
                        <div class="lubricar-order-total">{{ $this->formatPrice($pedido['total'] ?? 0) }}</div>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($detalle)
            <div class="lubricar-pedido-modal-backdrop" wire:click="cerrarDetalle">
                <div class="lubricar-pedido-modal" wire:click.stop>
                    <div class="lubricar-section-head">
                        <div>
                            <h3 class="lubricar-section-title">Pedido #{{ $detalle['numero'] ?? $detalle['id'] }}</h3>
                            <p class="lubricar-section-sub">
                                <span @class(['lubricar-badge', 'lubricar-badge--'.$this->estadoColor($detalle['estado'] ?? '')])>
                                    {{ $detalle['estado_label'] ?? ucfirst($detalle['estado'] ?? '') }}
                                </span>
                            </p>
                        </div>
                        <button type="button" class="lubricar-pager-btn" wire:click="cerrarDetalle">Cerrar</button>
                    </div>

                    <div class="lubricar-orders" style="margin-top: 1rem;">
                        @foreach (($detalle['items'] ?? []) as $item)
                            <div class="lubricar-order-row">
                                <div>
                                    <div class="lubricar-order-num">{{ $item['nombre'] ?? 'Producto' }}</div>
                                    <div class="lubricar-order-date">
                                        {{ $item['marca'] ?? '' }} · x{{ $item['cantidad'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="lubricar-order-total">{{ $this->formatPrice($item['subtotal'] ?? 0) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="lubricar-cart-total" style="margin-top: 1rem;">
                        <span>Total</span>
                        <strong>{{ $this->formatPrice($detalle['total'] ?? 0) }}</strong>
                    </div>

                    @if (! empty($detalle['observaciones']))
                        <p class="lubricar-section-sub" style="margin-top: 0.75rem;">
                            Obs: {{ $detalle['observaciones'] }}
                        </p>
                    @endif

                    <div class="lubricar-pedido-actions">
                        @if ($detalle['editable'] ?? false)
                            <button type="button" class="lubricar-cart-close-btn" wire:click="editarPedido({{ $detalle['id'] }})">
                                Modificar pedido
                            </button>
                            <button type="button" class="lubricar-cart-clear" wire:click="cancelarPedido({{ $detalle['id'] }})" wire:confirm="¿Cancelar este pedido? Se devolverá el stock en GESTIÓN.">
                                Cancelar pedido
                            </button>
                        @else
                            <p class="lubricar-section-sub">Este pedido ya no se puede modificar.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
