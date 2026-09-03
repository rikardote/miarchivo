<?php

namespace App\Models;

use App\Enums\ExpedientStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Expedient extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Expediente creado: {$this->expedient_code}",
                'updated' => "Expediente actualizado: {$this->expedient_code}",
                'deleted' => "Expediente eliminado: {$this->expedient_code}",
                default => "Actividad en expediente: {$eventName}"
            });
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

    public function loans(): HasMany
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
        $search = trim($search);

        return $query->where(function ($q) use ($search) {
            $q->where('expedient_code', 'like', "%{$search}%")
                ->orWhereHas('employee', function ($eq) use ($search) {
                    $eq->where('rfc', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", ["%{$search}%"]);
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

    public function getQrContentAttribute(): string
    {
        return $this->expedient_code;
    }

    public function getSlugAttribute(): string
    {
        $employee = $this->relationLoaded('employee') ? $this->employee : $this->employee()->first();
        if ($employee) {
            $nameSlug = Str::slug("{$employee->last_name} {$employee->first_name}");

            return $nameSlug ? "{$this->expedient_code}-{$nameSlug}" : $this->expedient_code;
        }

        return $this->expedient_code;
    }

    public function getRouteKey(): mixed
    {
        return $this->slug;
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if (is_numeric($value)) {
            $found = $this->where($field ?? 'id', $value)->first();
            if ($found) {
                return $found;
            }
        }

        $exact = $this->where('expedient_code', $value)->first();
        if ($exact) {
            return $exact;
        }

        $parts = explode('-', (string) $value);
        $candidate = '';
        foreach ($parts as $part) {
            $candidate = $candidate === '' ? $part : "{$candidate}-{$part}";
            $found = $this->where('expedient_code', $candidate)->first();
            if ($found) {
                return $found;
            }
        }

        return null;
    }
}
