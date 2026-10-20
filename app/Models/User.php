<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Este panel es el portal B2B (clientes). Admin/vendedor también entran en etapa de pruebas.
        return (bool) $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isVendedor(): bool
    {
        return $this->role === UserRole::Vendedor;
    }

    public function isCliente(): bool
    {
        return $this->role === UserRole::Cliente;
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=dff7f5&color=0d9b9b&bold=true&format=svg";
    }
}
