<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Cuenta extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static string $view = 'filament.pages.placeholder';

    protected static ?string $navigationLabel = 'Cuenta corriente';

    protected static ?string $title = 'Cuenta corriente';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    public function getSubheading(): ?string
    {
        return 'Saldo e historial de movimientos de tu cuenta.';
    }
}
