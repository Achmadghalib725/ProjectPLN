<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    // Relasi: User milik satu Gudang (kecuali Admin)
    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }
}