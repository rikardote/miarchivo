<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.empleados.url', env('EMPLOYEES_API_URL', 'http://host.docker.internal:9290/api'));
    }

    /**
     * Search employees in the external API.
     */
    public function search(string $query): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/employees/search", [
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
    public function syncEmployee(array $apiData): ?Employee
    {
        if (empty($apiData['id_legal'])) {
            return null;
        }

        $cleanRfc = mb_strtoupper(mb_substr(trim($apiData['id_legal']), 0, 10), 'UTF-8');
        if (empty($cleanRfc)) {
            return null;
        }

        // Determine status from API (map 'ACTIVO' to 'active', otherwise 'inactive')
        $apiStatus = strtoupper($apiData['estado_empleado'] ?? $apiData['estatus'] ?? 'ACTIVO');
        $status = (str_contains($apiStatus, 'ACTIVO')) ? 'active' : 'inactive';

        $employee = Employee::where('rfc', $cleanRfc)->first();

        $data = [
            'external_api_id' => $apiData['id'] ?? null,
            'employee_number' => $apiData['id_empleado'] ?? null,
            'first_name' => $apiData['nombre'] ?? '',
            'last_name' => trim(($apiData['apellido_1'] ?? '') . ' ' . ($apiData['apellido_2'] ?? '')),
            'position' => $apiData['n_puesto_plaza'] ?? null,
            'work_center' => $apiData['n_centro_trabajo'] ?? null,
            'city' => $apiData['poblacion'] ?? null,
            'employment_status' => $status,
            'last_synced_at' => now(),
        ];

        // Only assign branch if it's a new employee
        if (!$employee) {
            $data['branch_id'] = $this->determineBranch($apiData, $status);
            return Employee::create(array_merge(['rfc' => $cleanRfc], $data));
        }

        $employee->update($data);
        return $employee;
    }

    /**
     * Helper to guess the branch for NEW employees only.
     */
    protected function determineBranch(array $apiData, string $status): ?int
    {
        // If employee is inactive (baja), they go to RH ALMANCEN (CEN)
        if ($status === 'inactive') {
            return Branch::where('code', 'CEN')->value('id');
        }

        // Active employees go to RH DELEGACION ESTATAL (MEX)
        return Branch::where('code', 'MEX')->value('id');
    }
}
