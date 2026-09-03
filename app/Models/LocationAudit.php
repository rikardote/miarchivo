<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'archive_location_id',
        'user_id',
        'expected_count',
        'scanned_count',
        'correct_count',
        'missing_count',
        'misplaced_count',
        'details',
        'notes',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(ArchiveLocation::class, 'archive_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
