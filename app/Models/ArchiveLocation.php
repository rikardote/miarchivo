<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function audits(): HasMany
    {
        return $this->hasMany(LocationAudit::class)->latest();
    }

    public function latestAudit(): HasOne
    {
        return $this->hasOne(LocationAudit::class)->latestOfMany();
    }

    // Accessors

    public function getFullLabelAttribute(): string
    {
        $branchName = $this->relationLoaded('branch') ? $this->branch?->name : null;

        $parts = array_filter([
            $branchName,
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

    public static function findByInitialLetter(string $letter, ?int $branchId = null): ?self
    {
        $letter = mb_strtoupper(mb_substr(trim($letter), 0, 1), 'UTF-8');
        if (empty($letter) || ! preg_match('/^[A-ZÑ]$/u', $letter)) {
            return null;
        }

        $query = static::where('is_active', true);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $locations = $query->get();

        foreach ($locations as $location) {
            $range = strtoupper(trim($location->alpha_range ?? ''));
            if (empty($range)) {
                continue;
            }

            // Pattern: "A - C", "A-C", "A - Z", "D - G"
            if (preg_match('/^([A-ZÑ])\s*[-–—]\s*([A-ZÑ])$/u', $range, $m)) {
                $start = $m[1];
                $end = $m[2];

                if (strcmp($letter, $start) >= 0 && strcmp($letter, $end) <= 0) {
                    return $location;
                }
            } elseif (str_contains($range, $letter)) {
                return $location;
            }
        }

        return null;
    }
}
