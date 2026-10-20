<?php

namespace App\Models;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'specs',
        'image_path',
        'precio_lista',
        'stock_disponible',
        'stock_reservado',
        'is_active',
    ];

    protected $casts = [
        'precio_lista' => 'decimal:2',
        'stock_disponible' => 'integer',
        'stock_reservado' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockFisico(): int
    {
        return $this->stock_disponible + $this->stock_reservado;
    }

    /**
     * Precio neto (lista - descuento), sin IVA.
     */
    public function precioNeto(?Client $client = null): float
    {
        return app(PricingService::class)->precioNeto((float) $this->precio_lista, $client);
    }

    /**
     * Precio final con IVA.
     */
    public function precioConIva(?Client $client = null): float
    {
        return app(PricingService::class)->precioConIva((float) $this->precio_lista, $client);
    }
}
