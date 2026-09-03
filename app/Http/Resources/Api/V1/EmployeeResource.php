<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'employee_number' => $this->employee_number,
            'rfc' => $this->rfc,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'position' => $this->position,
            'work_center' => $this->work_center,
            'city' => $this->city,
            'department_id' => $this->department_id,
            'department_name' => $this->department?->name,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'employment_status' => $this->employment_status,
        ];
    }
}
