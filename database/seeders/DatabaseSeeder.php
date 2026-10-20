<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@lubricar.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        Setting::set('descuento_global', 0);
        Setting::set('iva_porcentaje', 21);

        Category::query()->updateOrCreate(
            ['slug' => 'lubricantes'],
            [
                'name' => 'Lubricantes',
                'description' => 'Rubro inicial',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
