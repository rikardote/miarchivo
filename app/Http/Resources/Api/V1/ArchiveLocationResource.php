<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchiveLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'location_type' => $this->location_type,
            'archive_name' => $this->archive_name,
            'cabinet' => $this->cabinet,
            'drawer' => $this->drawer,
            'alpha_range' => $this->alpha_range,
            'notes' => $this->notes,
            'full_label' => $this->full_label,
            'short_label' => $this->short_label,
            'is_active' => $this->is_active,
            'expedients_count' => $this->whenCounted('expedients'),
        ];
    }
}
