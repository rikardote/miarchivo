<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpedientMovement extends Model
{
    protected $fillable = [
        'expedient_id',
        'user_id',
        'movement_type',
        'from_location_id',
        'to_location_id',
        'notes',
    ];

    protected $casts = [
        'movement_type' => MovementType::class,
    ];

    // This model is immutable — no updates or deletes
    public static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Los movimientos de expediente son inmutables.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Los movimientos de expediente no pueden eliminarse.');
        });
    }

    // Relationships

    public function expedient(): BelongsTo
    {
        return $this->belongsTo(Expedient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(ArchiveLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(ArchiveLocation::class, 'to_location_id');
    }
}
