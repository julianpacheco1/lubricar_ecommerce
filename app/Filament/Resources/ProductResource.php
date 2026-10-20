<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Catálogo';

    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'productos';

    protected static ?string $navigationGroup = null;

    /** Catálogo real viene de GESTIÓN (página Catalogo). */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Rubro')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('sku')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('specs')
                    ->label('Especificaciones')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('precio_lista')
                    ->label('Precio lista (sin IVA)')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->helperText('Se guarda sin IVA. El descuento e IVA se aplican al vender.'),
                Forms\Components\TextInput::make('stock_disponible')
                    ->label('Stock disponible')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('stock_reservado')
                    ->label('Stock reservado')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Se actualiza con los pedidos.'),
                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagen')
                    ->image()
                    ->directory('products')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Img')
                    ->square(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Rubro')
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_lista')
                    ->label('Lista s/IVA')
                    ->money('ARS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_neto')
                    ->label('Neto s/IVA')
                    ->getStateUsing(fn (Product $record) => $record->precioNeto())
                    ->money('ARS'),
                Tables\Columns\TextColumn::make('stock_disponible')
                    ->label('Disp.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_reservado')
                    ->label('Res.')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Rubro')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
