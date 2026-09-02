<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\EmployeeApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncEmployees extends Command
{
    protected $signature = 'employees:sync 
        {--rfc= : Sync a specific employee by RFC} 
        {--periodo= : Sincronizar empleados de una quincena específica (ej. 16-2026)} 
        {--latest-period : Sincronizar automáticamente la quincena más reciente} 
        {--missing-historical : Sincronizar empleados de quincenas anteriores que faltan en la última}
        {--max-pages= : Maximum pages to sync} 
        {--per-page=250 : Cantidad de registros por página}';

    protected $description = 'Sync employees from the external HR API';

    public function handle(EmployeeApiService $apiService)
    {
        $rfc = $this->option('rfc');
        $maxPages = $this->option('max-pages');
        $perPage = (int) ($this->option('per-page') ?: 250);
        $periodo = $this->option('periodo');
        $latestPeriod = $this->option('latest-period');
        $missingHistorical = $this->option('missing-historical');

        $baseUrl = config('services.empleados.url', env('EMPLOYEES_API_URL', 'http://host.docker.internal:9290/api'));
        $apiKey = config('services.empleados.api_key');
        $headers = $apiKey ? ['X-API-KEY' => $apiKey] : [];

        if ($rfc) {
            $this->info("Searching for employee with RFC: {$rfc}");
            $results = $apiService->search($rfc);

            if (empty($results)) {
                $this->error('Employee not found.');

                return 1;
            }

            $employee = $apiService->syncEmployee($results[0]);
            $this->info("Synced: {$employee->full_name} ({$employee->rfc})");

            return 0;
        }

        if ($missingHistorical) {
            $this->info('Consultando quincenas disponibles en la API...');
            $periodsResp = Http::withHeaders($headers)->get("{$baseUrl}/employees/periods");
            if (! $periodsResp->successful()) {
                $this->error('No fue posible consultar las quincenas de la API.');

                return 1;
            }

            $periods = $periodsResp->json();
            sort($periods);
            $latest = end($periods);
            $historicalPeriods = array_reverse(array_filter($periods, fn ($p) => $p !== $latest));

            $this->info("Quincena más reciente activa: [{$latest}].");
            $this->info('Buscando empleados ausentes en quincenas anteriores ('.implode(', ', $historicalPeriods).')...');

            $newlyAdded = 0;
            foreach ($historicalPeriods as $histPeriod) {
                $page = 1;
                $addedInPeriod = 0;
                do {
                    $resp = Http::withHeaders($headers)->get("{$baseUrl}/employees/search", [
                        'periodo' => $histPeriod,
                        'page' => $page,
                        'per_page' => $perPage,
                    ]);

                    if (! $resp->successful()) {
                        break;
                    }

                    $data = $resp->json();
                    $items = $data['data'] ?? [];
                    $lastPage = $data['last_page'] ?? 1;

                    DB::transaction(function () use ($items, $apiService, &$newlyAdded, &$addedInPeriod) {
                        foreach ($items as $item) {
                            $created = $apiService->syncEmployee($item, onlyIfMissing: true, forceStatus: 'inactive');
                            if ($created && $created->wasRecentlyCreated) {
                                $newlyAdded++;
                                $addedInPeriod++;
                            }
                        }
                    });

                    $page++;
                } while ($page <= $lastPage);

                $this->info("Quincena [{$histPeriod}]: se incorporaron {$addedInPeriod} empleados ausentes.");
            }

            $totalLocal = Employee::count();
            $this->info('¡Búsqueda histórica completada!');
            $this->info("Se importaron en total {$newlyAdded} empleados históricos faltantes (registrados como inactivos).");
            $this->info("Total global en catálogo local: {$totalLocal} empleados.");

            return 0;
        }

        // Si se pide la última quincena automáticamente
        if ($latestPeriod && empty($periodo)) {
            $this->info('Consultando quincenas disponibles en la API...');
            $periodsResp = Http::withHeaders($headers)->get("{$baseUrl}/employees/periods");
            if ($periodsResp->successful()) {
                $periods = $periodsResp->json();
                if (! empty($periods)) {
                    sort($periods);
                    $periodo = end($periods);
                    $this->info("Última quincena detectada: {$periodo}");
                }
            }
        }

        $endpoint = $periodo
            ? "{$baseUrl}/employees/search"
            : "{$baseUrl}/employees";

        $this->info($periodo
            ? "Iniciando sincronización para la quincena [{$periodo}] (per_page: {$perPage})..."
            : "Iniciando sincronización completa de la API (per_page: {$perPage})...");

        $page = 1;
        $syncedCount = 0;
        $totalFound = null;

        do {
            $queryParams = [
                'page' => $page,
                'per_page' => $perPage,
            ];

            if ($periodo) {
                $queryParams['periodo'] = $periodo;
            }

            $response = Http::withHeaders($headers)->get($endpoint, $queryParams);

            if (! $response->successful()) {
                $this->error('Failed to fetch from API. Status: '.$response->status());
                break;
            }

            $data = $response->json();
            $items = $data['data'] ?? [];
            $totalFound ??= ($data['total'] ?? null);
            $lastPage = $data['last_page'] ?? 1;

            $this->info("Procesando página {$page} de {$lastPage} (".count($items).' registros)...');

            DB::transaction(function () use ($items, $apiService, &$syncedCount) {
                foreach ($items as $item) {
                    if ($apiService->syncEmployee($item)) {
                        $syncedCount++;
                    }
                }
            });

            if ($maxPages && $page >= $maxPages) {
                $this->info("Reached max pages limit ({$maxPages}).");
                break;
            }

            $page++;

        } while ($page <= $lastPage);

        $totalLocal = Employee::count();
        $this->info('¡Sincronización completada exitosamente!');
        $this->info("Procesados: {$syncedCount} | Total empleados únicos en catálogo local: {$totalLocal}");

        return 0;
    }
}
