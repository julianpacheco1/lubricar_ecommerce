<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.manage-settings';

    protected static ?string $navigationLabel = 'Config';

    protected static ?string $title = 'Configuración';

    protected static ?string $navigationGroup = null;

    /** Oculto del menú del cliente; se puede abrir por URL si hace falta. */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'descuento_global' => Setting::descuentoGlobal(),
            'iva_porcentaje' => Setting::ivaPorcentaje(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Precios')
                    ->description('Fórmula: precio lista (sin IVA) − descuento + IVA. En el panel se trabaja sin IVA.')
                    ->schema([
                        Forms\Components\TextInput::make('descuento_global')
                            ->label('Descuento global (%)')
                            ->numeric()
                            ->required()
                            ->suffix('%')
                            ->helperText('Hoy todos los clientes usan este descuento.'),
                        Forms\Components\TextInput::make('iva_porcentaje')
                            ->label('IVA (%)')
                            ->numeric()
                            ->required()
                            ->suffix('%'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('descuento_global', $data['descuento_global']);
        Setting::set('iva_porcentaje', $data['iva_porcentaje']);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
