<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nim',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Pengajuan yang dibuat oleh pengguna.
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Perubahan status yang dilakukan oleh pengguna.
    public function bookingHistories(): HasMany
    {
        return $this->hasMany(BookingHistory::class, 'changed_by');
    }
}