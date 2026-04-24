<?php

namespace App\Models;

use App\Enums\ExpedientStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Expedient extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'employee_id',
        'expedient_code',
        'volume_number',
        'current_status',
        'current_location_id',
        'current_holder_id',
        'qr_code',
        'barcode',
        'opened_at',
        'closed_at',
        'is_active',
    ];

    protected $casts = [
        'current_status' => ExpedientStatus::class,
        'opened_at' => 'date',
        'closed_at' => 'date',
        'is_active' => 'boolean',
        'volume_number' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['current_status', 'current_location_id', 'current_holder_id'])
            ->logOnlyDirty();
    }

    // Relationships

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(ArchiveLocation::class, 'current_location_id');
    }

    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    public function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(ExpedientMovement::class)->orderByDesc('created_at');
    }

    // Scopes

    public function scopeAvailable($query)
    {
        return $query->where('current_status', ExpedientStatus::Available);
    }

    public function scopeLoaned($query)
    {
        return $query->where('current_status', ExpedientStatus::Loaned);
    }

    public function scopeOverdue($query)
    {
        return $query->where('current_status', ExpedientStatus::Loaned)
            ->whereHas('loanRequests', function ($q) {
                $q->where('status', 'delivered')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now());
            });
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('expedient_code', 'like', "%{$search}%")
              ->orWhereHas('employee', function ($eq) use ($search) {
                  $eq->where('rfc', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
              });
        });
    }

    // Helpers

    public function isAvailable(): bool
    {
        return $this->current_status === ExpedientStatus::Available;
    }

    public function isLoaned(): bool
    {
        return $this->current_status === ExpedientStatus::Loaned;
    }

    public function activeLoan(): ?LoanRequest
    {
        return $this->loanRequests()
            ->whereIn('status', ['pending', 'approved', 'reserved', 'delivered'])
            ->latest()
            ->first();
    }
}
