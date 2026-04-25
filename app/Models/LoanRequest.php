<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class LoanRequest extends Model
{
    use LogsActivity;

    protected $fillable = [
        'expedient_id',
        'requester_id',
        'approved_by',
        'status',
        'requested_at',
        'approved_at',
        'reserved_at',
        'delivered_at',
        'returned_at',
        'due_date',
        'observations',
        'return_notes',
    ];

    protected $casts = [
        'status' => LoanStatus::class,
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'reserved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'approved_by', 'delivered_at', 'returned_at'])
            ->logOnlyDirty();
    }

    // Relationships

    public function expedient(): BelongsTo
    {
        return $this->belongsTo(Expedient::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->requester();
    }

    // Helpers

    public function isOverdue(): bool
    {
        return $this->status === LoanStatus::Delivered
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return (int) $this->due_date->diffInDays(now());
    }
}
