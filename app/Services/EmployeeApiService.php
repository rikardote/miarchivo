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
            $response = Http::get("{$this->baseUrl}/employees/search", [
                'q' => $query,
                'per_page' => 15,
            ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
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

        // Determine branch based on city or work center (basic mapping)
        $branchId = $this->determineBranch($apiData);

        return Employee::updateOrCreate(
            ['rfc' => $apiData['id_legal']],
            [
                'external_api_id' => $apiData['id'] ?? null,
                'employee_number' => $apiData['id_empleado'] ?? null,
                'first_name' => $apiData['nombre'] ?? '',
                'last_name' => trim(($apiData['apellido_1'] ?? '') . ' ' . ($apiData['apellido_2'] ?? '')),
                'position' => $apiData['n_puesto_plaza'] ?? null,
                'work_center' => $apiData['n_centro_trabajo'] ?? null,
                'city' => $apiData['poblacion'] ?? null,
                'branch_id' => $branchId,
                'employment_status' => 'active', // Assuming active if in API, could map cancelado field if needed
                'last_synced_at' => now(),
            ]
        );
    }

    /**
     * Helper to guess the branch based on location data.
     */
    protected function determineBranch(array $apiData): ?int
    {
        $city = strtoupper($apiData['poblacion'] ?? '');
        $workCenter = strtoupper($apiData['n_centro_trabajo'] ?? '');

        if (str_contains($city, 'MEXICALI') || str_contains($workCenter, 'MEXICALI')) {
            return Branch::where('code', 'MEX')->value('id');
        }

        if (str_contains($city, 'TIJUANA') || str_contains($workCenter, 'TIJUANA')) {
            return Branch::where('code', 'TIJ')->value('id');
        }

        return Branch::where('code', 'CEN')->value('id');
    }
}
