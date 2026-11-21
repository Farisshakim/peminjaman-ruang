<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    public function getAllocatedStockAttribute(): int
    {
        return (int) $this->rooms()
            ->sum('room_facilities.quantity');
    }

    public function getAvailableStockAttribute(): int
    {
        return (int) $this->total_stock - $this->allocated_stock;
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(
            Room::class,
            'room_facilities'
        )
            ->withPivot('quantity')
            ->withTimestamps();
    }

    protected $fillable = [
    'name',
    'total_stock',
];
}