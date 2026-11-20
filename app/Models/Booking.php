<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'activity_name',
        'purpose',
        'booking_date',
        'start_time',
        'end_time',
        'participant_count',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'participant_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BookingHistory::class);
    }
}