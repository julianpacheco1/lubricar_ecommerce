<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PanelOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Panel';

    protected static ?string $title = 'Panel';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    public function getSubheading(): ?string
    {
        return 'Resumen de tu cuenta, pedidos y accesos rápidos';
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [
            PanelOverview::class,
        ];
    }
}
