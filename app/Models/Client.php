<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'name',
        'trade_name',
        'cuit',
        'email',
        'phone',
        'address',
        'city',
        'notes',
        'discount_percent',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Descuento efectivo: propio del cliente o el global.
     */
    public function effectiveDiscount(): float
    {
        if ($this->discount_percent !== null) {
            return (float) $this->discount_percent;
        }

        return Setting::descuentoGlobal();
    }

    public function hasPortalAccess(): bool
    {
        return $this->user_id !== null;
    }
}
