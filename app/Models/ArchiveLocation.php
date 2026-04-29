<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArchiveLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'location_type',
        'archive_name',
        'cabinet',
        'drawer',
        'alpha_range',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function expedients(): HasMany
    {
        return $this->hasMany(Expedient::class, 'current_location_id');
    }

    // Accessors

    public function getFullLabelAttribute(): string
    {
        $parts = array_filter([
            $this->branch->name ?? null,
            $this->location_type,
            $this->archive_name,
            $this->cabinet ? "Gaveta {$this->cabinet}" : null,
            $this->drawer ? "Cajón {$this->drawer}" : null,
            $this->alpha_range,
        ]);

        return implode(' › ', $parts);
    }

    public function getShortLabelAttribute(): string
    {
        $parts = array_filter([
            $this->archive_name,
            $this->cabinet ? "Gaveta {$this->cabinet}" : null,
            $this->drawer ? "Cajón {$this->drawer}" : null,
            $this->alpha_range ? "({$this->alpha_range})" : null,
        ]);

        return implode(' - ', $parts);
    }
}
