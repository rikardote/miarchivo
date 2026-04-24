<?php

namespace App\Console\Commands;

use App\Services\EmployeeApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncEmployees extends Command
{
    protected $signature = 'employees:sync {--rfc= : Sync a specific employee by RFC} {--max-pages= : Maximum pages to sync}';
    protected $description = 'Sync employees from the external HR API';

    public function handle(EmployeeApiService $apiService)
    {
        $rfc = $this->option('rfc');
        $maxPages = $this->option('max-pages');

        if ($rfc) {
            $this->info("Searching for employee with RFC: {$rfc}");
            $results = $apiService->search($rfc);
            
            if (empty($results)) {
                $this->error("Employee not found.");
                return 1;
            }

            $employee = $apiService->syncEmployee($results[0]);
            $this->info("Synced: {$employee->full_name} ({$employee->rfc})");
            return 0;
        }

        $this->info('Starting full sync...');
        
        $baseUrl = config('services.empleados.url', env('EMPLOYEES_API_URL', 'http://host.docker.internal:9290/api'));
        
        $page = 1;
        $syncedCount = 0;
        
        do {
            $this->info("Fetching page {$page}...");
            $response = Http::get("{$baseUrl}/employees", [
                'page' => $page,
                'per_page' => 100,
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch from API. Status: " . $response->status());
                break;
            }

            $data = $response->json();
            $items = $data['data'] ?? [];

            foreach ($items as $item) {
                if ($apiService->syncEmployee($item)) {
                    $syncedCount++;
                }
            }

            $lastPage = $data['last_page'] ?? 1;
            
            if ($maxPages && $page >= $maxPages) {
                $this->info("Reached max pages limit ({$maxPages}).");
                break;
            }

            $page++;
            
        } while ($page <= $lastPage);

        $this->info("Sync completed! Synced {$syncedCount} employees.");
        return 0;
    }
}
