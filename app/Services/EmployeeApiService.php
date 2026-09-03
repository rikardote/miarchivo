<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeApiService
{
    protected string $baseUrl;

    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.empleados.url', env('EMPLOYEES_API_URL', 'http://host.docker.internal:9290/api'));
        $this->apiKey = config('services.empleados.api_key');
    }

    /**
     * Headers de autenticación para la API de empleados.
     */
    protected function headers(): array
    {
        return $this->apiKey
            ? ['X-API-KEY' => $this->apiKey]
            : [];
    }

    /**
     * Search employees in the external API.
     */
    public function search(string $query): array
    {
        try {
            $response = Http::timeout(5)->withHeaders($this->headers())->get("{$this->baseUrl}/employees/search", [
                'q' => $query,
                'per_page' => 15,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // If it's a wrapped response return the data, otherwise return the whole response
                return $data['data'] ?? $data ?? [];
            }

            Log::warning("EmployeeApiService: Failed to search '{$query}'. Status: {$response->status()}");
        } catch (\Exception $e) {
            Log::error("EmployeeApiService: Exception while searching '{$query}': {$e->getMessage()}");
        }

        return [];
    }

    /**
     * Sync a single employee from API data.
     */
    protected ?int $mexBranchId = null;

    protected ?int $cenBranchId = null;

    public function syncEmployee(array $apiData): ?Employee
    {
        $rawRfc = $apiData['id_legal'] ?? $apiData['rfc'] ?? null;
        if (empty($rawRfc)) {
            return null;
        }

        $cleanRfc = mb_strtoupper(mb_substr(trim($rawRfc), 0, 10), 'UTF-8');
        if (empty($cleanRfc)) {
            return null;
        }

        $employeeNumber = ! empty($apiData['id_empleado']) ? mb_strtoupper(trim((string) $apiData['id_empleado']), 'UTF-8') : (! empty($apiData['employee_number']) ? mb_strtoupper(trim((string) $apiData['employee_number']), 'UTF-8') : null);
        $externalApiId = ! empty($apiData['id']) ? (int) $apiData['id'] : (! empty($apiData['external_api_id']) ? (int) $apiData['external_api_id'] : null);

        // Determine status from API (map 'ACTIVO' to 'active', otherwise 'inactive')
        $apiStatus = strtoupper($apiData['estado_empleado'] ?? $apiData['estatus'] ?? $apiData['employment_status'] ?? 'ACTIVO');
        $status = (str_contains($apiStatus, 'ACTIVO')) ? 'active' : 'inactive';

        // Fast indexed lookup
        $employee = Employee::withTrashed()->where('rfc', $cleanRfc)->first();

        if (! $employee && $employeeNumber) {
            $employee = Employee::withTrashed()->where('employee_number', $employeeNumber)->first();
        }

        if (! $employee && $externalApiId) {
            $employee = Employee::withTrashed()->where('external_api_id', $externalApiId)->first();
        }

        if ($employee && $employee->trashed()) {
            $employee->restore();
        }

        $data = [
            'rfc' => $cleanRfc,
            'external_api_id' => $externalApiId,
            'employee_number' => $employeeNumber,
            'first_name' => $apiData['nombre'] ?? $apiData['first_name'] ?? '',
            'last_name' => trim(($apiData['apellido_1'] ?? $apiData['last_name'] ?? '').' '.($apiData['apellido_2'] ?? '')),
            'position' => $apiData['n_puesto_plaza'] ?? $apiData['position'] ?? null,
            'work_center' => $apiData['n_centro_trabajo'] ?? $apiData['work_center'] ?? null,
            'city' => $apiData['poblacion'] ?? $apiData['city'] ?? null,
            'employment_status' => $status,
            'last_synced_at' => now(),
        ];

        // Only assign branch if it's a new employee
        if (! $employee) {
            $data['branch_id'] = $this->determineBranch($apiData, $status);

            return Employee::create($data);
        }

        $employee->update($data);

        return $employee;
    }

    /**
     * Helper to guess the branch for NEW employees only.
     */
    protected function determineBranch(array $apiData, string $status): ?int
    {
        if ($this->mexBranchId === null) {
            $this->mexBranchId = Branch::where('code', 'MEX')->value('id');
            $this->cenBranchId = Branch::where('code', 'CEN')->value('id');
        }

        // If employee is inactive (baja), they go to RH ALMANCEN (CEN)
        if ($status === 'inactive') {
            return $this->cenBranchId;
        }

        // Active employees go to RH DELEGACION ESTATAL (MEX)
        return $this->mexBranchId;
    }
}
