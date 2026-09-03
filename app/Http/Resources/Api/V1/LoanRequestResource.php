<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanRequestResource extends JsonResource
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
            'expedient_id' => $this->expedient_id,
            'expedient' => new ExpedientResource($this->whenLoaded('expedient')),
            'requester' => $this->requester ? [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'email' => $this->requester->email,
            ] : null,
            'approver' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'requested_at' => $this->requested_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'reserved_at' => $this->reserved_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'due_date' => $this->due_date?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'days_overdue' => $this->daysOverdue(),
            'observations' => $this->observations,
            'delivery_notes' => $this->delivery_notes,
            'return_notes' => $this->return_notes,
        ];
    }
}
