<?php

/**
 * Marca LubriCar.
 * - Web marketing: oscura (lubricarsrl.com)
 * - Este panel (admin/vendedor): "panel liviano" claro + acento teal
 */
return [
    'name' => 'LubriCar',

    // Identidad (misma que la web)
    'colors' => [
        'accent' => '#03d8c8',
        'accent_soft' => 'rgba(3, 216, 200, 0.12)',
        'ok' => '#3dce8a',
        // Panel liviano (vista interna)
        'panel_bg' => '#f4f6f8',
        'panel_surface' => '#ffffff',
        'panel_ink' => '#1a1f28',
        'panel_muted' => '#6b7280',
        'panel_line' => '#e5e7eb',
        // Web oscura (referencia; portal cliente se define aparte)
        'web_bg' => '#0b0d10',
        'web_ink' => '#f2f4f7',
    ],

    'fonts' => [
        'display' => 'Barlow Condensed',
        'body' => 'Manrope',
    ],

    'logo' => [
        // Sobre fondos claros (este panel)
        'on_light' => 'images/brand/logo-lubricar-negro.png',
        // Sobre fondos oscuros
        'on_dark' => 'images/brand/logo-lubricar-blanco.png',
        'default' => 'images/brand/logo-lubricar.png',
    ],

    'favicon' => 'favicon.ico',
];
