<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\HasStringId;

    // Handled by trait
    
    // protected $connection = 'mysql';
    protected $fillable = ['name','email','password'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
