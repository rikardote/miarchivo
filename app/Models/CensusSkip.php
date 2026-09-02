<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CensusSkip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'archive_location_id',
        'user_id',
        'reason',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(ArchiveLocation::class, 'archive_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
