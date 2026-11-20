<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHistory extends Model
{
    // Tabel riwayat hanya memiliki created_at.
    public const UPDATED_AT = null;

    protected $fillable = [
        'changed_by',
        'previous_status',
        'new_status',
        'notes',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}