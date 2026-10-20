<x-filament-widgets::widget>
    <div class="lubricar-panel">
        {{-- Bienvenida --}}
        <div class="lubricar-welcome">
            <div>
                <h2 class="lubricar-welcome-title">{{ $this->getGreeting() }}</h2>
                <p class="lubricar-welcome-text">
                    Acá tenés un resumen de tu cuenta, pedidos y acceso rápido al catálogo.
                </p>
            </div>
            <a href="{{ \App\Filament\Pages\Catalogo::getUrl() }}" class="lubricar-welcome-cta">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5" />
                Buscar productos
            </a>
        </div>

        {{-- Resumen --}}
        <div class="lubricar-panel-grid">
            @foreach ($this->getSummaryCards() as $card)
                <article @class([
                    'lubricar-card',
                    'lubricar-card--warn' => ($card['tone'] ?? '') === 'warn',
                    'lubricar-card--ok' => ($card['tone'] ?? '') === 'ok',
                ])>
                    <div class="lubricar-card-top">
                        <div class="lubricar-card-copy">
                            <span class="lubricar-card-title">{{ $card['title'] }}</span>
                            <div class="lubricar-card-value">{{ $card['value'] }}</div>
                            <div class="lubricar-card-meta">{{ $card['meta'] }}</div>
                        </div>
                        <div class="lubricar-card-icon">
                            <x-filament::icon :icon="$card['icon']" />
                        </div>
                    </div>

                    <div class="lubricar-card-footer">
                        <a href="{{ $card['url'] }}" class="lubricar-card-link">
                            <span>{{ $card['link_label'] }}</span>
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pedidos recientes --}}
        <section class="lubricar-section">
            <div class="lubricar-section-head">
                <div>
                    <h3 class="lubricar-section-title">Últimos pedidos</h3>
                    <p class="lubricar-section-sub">Estado de tus pedidos más recientes</p>
                </div>
                <a href="{{ \App\Filament\Pages\Pedidos::getUrl() }}" class="lubricar-section-link">
                    Ver todos
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                </a>
            </div>

            @php $orders = $this->getRecentOrders(); @endphp

            @if (count($orders) === 0)
                <div class="lubricar-empty">
                    <div class="lubricar-empty-icon">
                        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-7 w-7" />
                    </div>
                    <p class="lubricar-empty-title">Todavía no tenés pedidos</p>
                    <p class="lubricar-empty-text">
                        Cuando armes un pedido desde el catálogo, va a aparecer acá con su estado
                        (pendiente, aceptado o cancelado).
                    </p>
                    <a href="{{ \App\Filament\Pages\Catalogo::getUrl() }}" class="lubricar-empty-cta">
                        Ir al catálogo
                    </a>
                </div>
            @else
                <div class="lubricar-orders">
                    @foreach ($orders as $order)
                        <div class="lubricar-order-row">
                            <div>
                                <div class="lubricar-order-num">#{{ $order['numero'] }}</div>
                                <div class="lubricar-order-date">{{ $order['fecha'] }}</div>
                            </div>
                            <span @class(['lubricar-badge', 'lubricar-badge--'.$order['estado_color']])>
                                {{ $order['estado'] }}
                            </span>
                            <div class="lubricar-order-total">{{ $order['total'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-filament-widgets::widget>
