<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Employee extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'external_api_id',
        'employee_number',
        'rfc',
        'first_name',
        'last_name',
        'position',
        'work_center',
        'city',
        'department_id',
        'branch_id',
        'employment_status',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rfc', 'first_name', 'last_name', 'employment_status'])
            ->logOnlyDirty();
    }

    // Relationships

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function expedients(): HasMany
    {
        return $this->hasMany(Expedient::class);
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('rfc', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('employee_number', 'like', "%{$search}%");
        });
    }
}
