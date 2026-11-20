<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'name',
        'location',
        'capacity',
        'description',
        'photo',
        'opening_time',
        'closing_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Facility::class,
            'room_facilities'
        )
            ->withPivot('quantity')
            ->withTimestamps();
    }
}