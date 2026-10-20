<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Setting;

/**
 * Fórmula: precio_lista (sin IVA) → aplica descuento → + IVA.
 *
 * En panel se trabaja y muestra sin IVA.
 * precio_neto = precio_lista * (1 - descuento/100)
 * precio_con_iva = precio_neto * (1 + iva/100)
 */
class PricingService
{
    public function descuento(?Client $client = null): float
    {
        if ($client) {
            return $client->effectiveDiscount();
        }

        return Setting::descuentoGlobal();
    }

    public function ivaPorcentaje(): float
    {
        return Setting::ivaPorcentaje();
    }

    public function precioNeto(float $precioLista, ?Client $client = null): float
    {
        $descuento = $this->descuento($client);

        return round($precioLista * (1 - ($descuento / 100)), 2);
    }

    public function montoIva(float $precioLista, ?Client $client = null): float
    {
        $neto = $this->precioNeto($precioLista, $client);

        return round($neto * ($this->ivaPorcentaje() / 100), 2);
    }

    public function precioConIva(float $precioLista, ?Client $client = null): float
    {
        $neto = $this->precioNeto($precioLista, $client);

        return round($neto * (1 + ($this->ivaPorcentaje() / 100)), 2);
    }
}
