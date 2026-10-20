<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| El portal cliente vive en Filament (raíz /).
| No definir GET / acá: pisa el dashboard de Filament.
|
*/

Route::redirect('/admin', '/');
Route::redirect('/admin/login', '/login');
