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

    public function managedGudangs()
    {
        return $this->belongsToMany(Gudang::class, 'gudang_user');
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->appNotifications()->unread();
    }

    /**
     * Get display name for a role
     */
    public static function roleDisplayName(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'operator_gudang' => 'Tool Man',
            'security' => 'Security',
            'manager' => 'Manager',
            'penerima' => 'Penerima',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    /**
     * Get all role display names
     */
    public static function roleDisplayNames(): array
    {
        return [
            'admin' => 'Admin',
            'operator_gudang' => 'Tool Man',
            'security' => 'Security',
            'manager' => 'Manager',
            'penerima' => 'Penerima',
        ];
    }

    /**
     * Accessor for role display name
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return self::roleDisplayName($this->role);
    }
}
