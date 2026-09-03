<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Employee;
use App\Services\EmployeeApiService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Facades\Activity;

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

    public function handle(EmployeeApiService $apiService): int
    {
        Activity::disableLogging();

        $rfc = $this->option('rfc');
        $maxPages = $this->option('max-pages') ? (int) $this->option('max-pages') : null;
        $perPage = (int) ($this->option('per-page') ?: 250);
        $periodo = $this->option('periodo');
        $latestPeriod = $this->option('latest-period');
        $missingHistorical = $this->option('missing-historical');

        $baseUrl = config('services.empleados.url', env('EMPLOYEES_API_URL', 'http://host.docker.internal:9290/api'));
        $apiKey = config('services.empleados.api_key');
        $headers = $apiKey ? ['X-API-KEY' => $apiKey] : [];

        $mexId = Branch::where('code', 'MEX')->value('id') ?? 1;
        $cenId = Branch::where('code', 'CEN')->value('id') ?? 2;

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
                            $created = $apiService->syncEmployee($item, onlyIfMissing: true, forceStatus: null);
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
            $this->info("Se importaron en total {$newlyAdded} empleados de quincenas anteriores que no estaban en la última.");
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
            ? "Iniciando sincronización para la quincena [{$periodo}]..."
            : "Iniciando sincronización completa de la API...");

        $queryParams = ['page' => 1, 'per_page' => 50];
        if ($periodo) {
            $queryParams['periodo'] = $periodo;
        }

        $initialResponse = Http::withHeaders($headers)->get($endpoint, $queryParams);

        if (! $initialResponse->successful()) {
            $this->error('Failed to connect to API. Status: '.$initialResponse->status());

            return 1;
        }

        $initData = $initialResponse->json();
        $lastPage = $initData['last_page'] ?? 1;
        $totalItems = $initData['total'] ?? 0;

        $targetPages = $maxPages ? min($maxPages, $lastPage) : $lastPage;
        $this->info("Total pages to sync: {$targetPages} (Total records: {$totalItems})");

        $bar = $this->output->createProgressBar($targetPages);
        $bar->start();

        $syncedCount = 0;
        $chunkSize = 10;

        for ($chunkStart = 1; $chunkStart <= $targetPages; $chunkStart += $chunkSize) {
            $chunkEnd = min($chunkStart + $chunkSize - 1, $targetPages);
            $pages = range($chunkStart, $chunkEnd);

            $responses = Http::pool(function ($pool) use ($pages, $endpoint, $headers, $periodo) {
                $requests = [];
                foreach ($pages as $p) {
                    $pParams = ['page' => $p, 'per_page' => 50];
                    if ($periodo) {
                        $pParams['periodo'] = $periodo;
                    }
                    $requests[] = $pool->withHeaders($headers)->get($endpoint, $pParams);
                }

                return $requests;
            });

            $batchItems = [];
            foreach ($responses as $res) {
                if ($res instanceof Response && $res->successful()) {
                    $items = $res->json()['data'] ?? [];
                    foreach ($items as $item) {
                        $batchItems[] = $item;
                    }
                }
                $bar->advance();
            }

            if (! empty($batchItems)) {
                $syncedCount += $this->syncBatch($batchItems, $mexId, $cenId);
            }
        }

        $bar->finish();
        $this->newLine();

        $uniqueCount = Employee::count();
        $this->info("Sync completed! Processed {$syncedCount} employee updates. Total unique employees in DB: {$uniqueCount}.");

        return 0;
    }
        return 0;
    }

    /**
     * Bulk upsert employees batch in a single query.
     */
    protected function syncBatch(array $items, int $mexId, int $cenId): int
    {
        $rows = [];
        $now = now();

        foreach ($items as $item) {
            $rawRfc = $item['id_legal'] ?? $item['rfc'] ?? null;
            if (empty($rawRfc)) {
                continue;
            }

            $cleanRfc = mb_strtoupper(mb_substr(trim($rawRfc), 0, 10), 'UTF-8');
            if (empty($cleanRfc)) {
                continue;
            }

            $empNum = ! empty($item['id_empleado']) ? mb_strtoupper(trim((string) $item['id_empleado']), 'UTF-8') : (! empty($item['employee_number']) ? mb_strtoupper(trim((string) $item['employee_number']), 'UTF-8') : null);
            $extId = ! empty($item['id']) ? (int) $item['id'] : (! empty($item['external_api_id']) ? (int) $item['external_api_id'] : null);
            $apiStatus = strtoupper($item['estado_empleado'] ?? $item['estatus'] ?? $item['employment_status'] ?? 'ACTIVO');
            $status = (str_contains($apiStatus, 'ACTIVO')) ? 'active' : 'inactive';

            $rows[$cleanRfc] = [
                'rfc' => $cleanRfc,
                'external_api_id' => $extId,
                'employee_number' => $empNum,
                'first_name' => mb_strtoupper(trim($item['nombre'] ?? $item['first_name'] ?? ''), 'UTF-8'),
                'last_name' => mb_strtoupper(trim(($item['apellido_1'] ?? $item['last_name'] ?? '').' '.($item['apellido_2'] ?? '')), 'UTF-8'),
                'position' => ! empty($item['n_puesto_plaza'] ?? $item['position']) ? mb_strtoupper(trim($item['n_puesto_plaza'] ?? $item['position']), 'UTF-8') : null,
                'work_center' => ! empty($item['n_centro_trabajo'] ?? $item['work_center']) ? mb_strtoupper(trim($item['n_centro_trabajo'] ?? $item['work_center']), 'UTF-8') : null,
                'city' => ! empty($item['poblacion'] ?? $item['city']) ? mb_strtoupper(trim($item['poblacion'] ?? $item['city']), 'UTF-8') : null,
                'employment_status' => $status,
                'branch_id' => ($status === 'inactive') ? $cenId : $mexId,
                'last_synced_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            return 0;
        }

        Employee::upsert(array_values($rows), ['rfc'], [
            'external_api_id', 'employee_number', 'first_name', 'last_name',
            'position', 'work_center', 'city', 'employment_status',
            'branch_id', 'last_synced_at', 'updated_at',
        ]);

        return count($rows);
    }
}
