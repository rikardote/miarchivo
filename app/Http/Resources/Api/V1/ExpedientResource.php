<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpedientResource extends JsonResource
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
            'expedient_code' => $this->expedient_code,
            'volume_number' => $this->volume_number,
            'current_status' => $this->current_status?->value,
            'current_status_label' => $this->current_status?->label(),
            'current_status_color' => $this->current_status?->color(),
            'current_location' => new ArchiveLocationResource($this->whenLoaded('currentLocation')),
            'current_holder' => $this->currentHolder ? [
                'id' => $this->currentHolder->id,
                'name' => $this->currentHolder->name,
                'email' => $this->currentHolder->email,
            ] : null,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'barcode' => $this->barcode,
            'qr_code' => $this->qr_code,
            'qr_content' => $this->qr_content,
            'opened_at' => $this->opened_at?->format('Y-m-d'),
            'closed_at' => $this->closed_at?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'movements' => ExpedientMovementResource::collection($this->whenLoaded('movements')),
            'active_loan' => new LoanRequestResource($this->whenLoaded('activeLoan')),
        ];
    }
}
