<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\CensusSkip;
use App\Models\Employee;
use App\Models\Expedient;
use App\Services\ExpedientService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

/**
 * Alta continua de expedientes: una sola pantalla para registrar de forma
 * masiva (uno por uno) los expedientes físicos pendientes de un cajón.
 *
 * Consume únicamente piezas existentes (ExpedientService::createExpedient,
 * la ruta de impresión de etiquetas y el modelo ArchiveLocation); no modifica
 * el flujo tradicional de alta (Expedients/Create).
 */
class ContinuousCreate extends Component
{
    use Toast;

    /** Gaveta / archivero elegida para la sesión (primer nivel de la cascada). */
    public ?string $selectedCabinet = null;

    /** Cajón destino de la sesión (ubicación física). */
    public ?int $location_id = null;

    /** Empleado actualmente en el visor (null = primero pendiente). */
    public ?int $currentEmployeeId = null;

    /** Indica que el expediente ya se creó y espera impresión/pegado. */
    public bool $readyToPrint = false;

    /** Expediente recién creado, cuya etiqueta se muestra para imprimir. */
    public ?int $lastCreatedExpedientId = null;

    /** Empleados aplazados (sin carpeta física a la vista en este lote). */
    public array $skippedIds = [];

    /** Control del modal y motivo de aplazamiento persistente. */
    public bool $showSkipModal = false;

    public string $skipReason = 'Carpeta física no localizada en lote';

    public string $customSkipReason = '';

    public function mount(): void
    {
        $this->authorize('create', Expedient::class);
    }

    public function updatedSelectedCabinet(): void
    {
        // Al cambiar de gaveta se descarta el cajón previo si no pertenece a ella.
        if ($this->location_id) {
            $location = ArchiveLocation::find($this->location_id);

            if (! $location || $location->cabinet !== $this->selectedCabinet) {
                $this->location_id = null;
            }
        }

        $this->resetSession();
    }

    public function updatedLocationId(): void
    {
        $this->resetSession();
    }

    /**
     * Limpia el progreso de la sesión cuando cambia la ubicación.
     * Cada expediente ya creado persiste en BD: solo se descarta la cola en memoria.
     */
    protected function resetSession(): void
    {
        $this->currentEmployeeId = null;
        $this->readyToPrint = false;
        $this->lastCreatedExpedientId = null;
        $this->skippedIds = [];
    }

    /**
     * Empleado a mostrar en el visor (el marcado, o el primero pendiente).
     */
    public function getCurrentEmployeeProperty(): ?Employee
    {
        if (! $this->location_id) {
            return null;
        }

        $query = $this->pendingQuery();

        if ($this->currentEmployeeId) {
            $employee = (clone $query)->whereKey($this->currentEmployeeId)->first();

            if ($employee) {
                return $employee;
            }
        }

        return $query->first();
    }

    /**
     * Empleado que sigue en la cola (el que se mostrará al avanzar), usado
     * como vista previa discreta debajo del visor principal.
     */
    public function getNextEmployeeProperty(): ?Employee
    {
        if (! $this->location_id || ! $this->currentEmployee) {
            return null;
        }

        $orderedIds = (clone $this->pendingQuery())->pluck('id')->all();

        $position = array_search($this->currentEmployee->id, $orderedIds);

        if ($position === false) {
            return null;
        }

        $nextId = $orderedIds[$position + 1] ?? null;

        return $nextId ? Employee::with('branch')->find($nextId) : null;
    }

    /**
     * Cola de empleados sin expediente, acotada al rango alfabético del cajón
     * elegido y excluyendo los aplazados en esta sesión o previamente en BD.
     */
    protected function pendingQuery(): Builder
    {
        $persistedSkippedIds = $this->location_id
            ? CensusSkip::where('archive_location_id', $this->location_id)
                ->where('status', 'deferred')
                ->pluck('employee_id')
                ->all()
            : [];

        $allSkipped = array_unique(array_merge($this->skippedIds, $persistedSkippedIds));

        if ($this->currentEmployeeId) {
            $allSkipped = array_diff($allSkipped, [$this->currentEmployeeId]);
        }

        $query = Employee::query()
            ->with('branch')
            ->whereDoesntHave('expedients')
            ->whereNotIn('id', $allSkipped)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($this->location_id) {
            $location = ArchiveLocation::find($this->location_id);
            $letters = $location ? $this->rangeLetters($location->alpha_range) : null;

            if ($letters !== null) {
                $query->whereIn(DB::raw('UPPER(SUBSTR(last_name, 1, 1))'), $letters);
            }
        }

        return $query;
    }

    /**
     * Convierte el rango alfabético de un cajón ('A - C') en su lista de
     * letras iniciales. Devuelve null cuando el cajón no tiene rango de letras
     * (p. ej. "DIRECTIVOS"): en ese caso la cola no se filtra por apellido.
     *
     * @return array<int, string>|null
     */
    protected function rangeLetters(?string $alphaRange): ?array
    {
        if ($alphaRange === null || trim($alphaRange) === '') {
            return null;
        }

        $range = strtoupper(trim($alphaRange));

        if (preg_match('/^([A-Z])\s*[-–—]\s*([A-Z])$/u', $range, $m)) {
            $start = $m[1];
            $end = $m[2];

            if (ord($start) > ord($end)) {
                return null;
            }

            $letters = [];
            for ($code = ord($start); $code <= ord($end); $code++) {
                $letters[] = chr($code);
            }

            return $letters;
        }

        if (preg_match('/^[A-Z]$/u', $range)) {
            return [$range];
        }

        return null;
    }

    /**
     * Crea el expediente del empleado visible (carpeta física en mano) en el
     * cajón elegido y prepara la impresión de su etiqueta.
     */
    public function createAndPrint(): void
    {
        $this->validate([
            'location_id' => ['required', 'exists:archive_locations,id'],
        ], [
            'location_id.required' => 'Selecciona un cajón para la sesión.',
            'location_id.exists' => 'La ubicación seleccionada no es válida.',
        ]);

        // Evita doble alta ante un doble clic o una petición en vuelo duplicada.
        if ($this->readyToPrint) {
            return;
        }

        $location = ArchiveLocation::find($this->location_id);

        if (! $location || ! $location->is_active) {
            $this->error('La ubicación seleccionada no está disponible.');

            return;
        }

        $employee = $this->currentEmployee;

        if (! $employee) {
            $this->warning('No hay empleados pendientes en este cajón.');

            return;
        }

        if ($employee->expedients()->exists()) {
            $this->warning("{$employee->full_name} ya cuenta con expediente.");
            $this->skipCurrent();

            return;
        }

        try {
            $expedient = app(ExpedientService::class)->createExpedient($employee, [
                'location_id' => $location->id,
            ]);
        } catch (QueryException $e) {
            $this->error("El expediente de {$employee->full_name} ya fue registrado (posible alta simultánea).");
            $this->skipCurrent();

            return;
        } catch (\Throwable $e) {
            $this->error('No fue posible crear el expediente: '.$e->getMessage());

            return;
        }

        $this->currentEmployeeId = $employee->id;
        $this->lastCreatedExpedientId = $expedient->id;
        $this->readyToPrint = true;

        // Resuelve automáticamente cualquier aplazamiento previo para este empleado
        CensusSkip::where('employee_id', $employee->id)
            ->where('status', 'deferred')
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        $this->skippedIds = array_values(array_filter(
            $this->skippedIds,
            fn ($id) => (int) $id !== $employee->id
        ));

        $this->success("Expediente {$expedient->expedient_code} creado.");
    }

    /**
     * Confirma que la etiqueta quedó pegada y avanza al siguiente pendiente.
     */
    public function confirmNext(): void
    {
        $this->lastCreatedExpedientId = null;
        $this->readyToPrint = false;
        $this->currentEmployeeId = null;
        unset($this->currentEmployee);
    }

    public function openSkipModal(): void
    {
        $this->skipReason = 'Carpeta física no localizada en lote';
        $this->customSkipReason = '';
        $this->showSkipModal = true;
    }

    public function confirmSkip(): void
    {
        $reason = $this->skipReason === 'Otro motivo'
            ? ($this->customSkipReason ?: 'Otro motivo no especificado')
            : $this->skipReason;

        $this->skipCurrent($reason);
        $this->showSkipModal = false;
    }

    /**
     * Aplaza al empleado visible (no se crea nada) y persiste el registro en BD.
     */
    public function skipCurrent(?string $reason = null): void
    {
        $employee = $this->currentEmployee;

        if ($employee && $this->location_id) {
            $effectiveReason = $reason ?: 'Carpeta física no localizada en lote';
            $this->skippedIds[] = $employee->id;

            CensusSkip::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'archive_location_id' => $this->location_id,
                    'status' => 'deferred',
                ],
                [
                    'user_id' => Auth::id() ?? 1,
                    'reason' => $effectiveReason,
                ]
            );

            $this->warning("{$employee->full_name} quedó registrado como aplazado.");
        }

        $this->lastCreatedExpedientId = null;
        $this->readyToPrint = false;
        $this->currentEmployeeId = null;
        unset($this->currentEmployee);
    }

    /**
     * Reincorpora a la cola un empleado previamente aplazado.
     */
    public function restoreSkipped(int $employeeId): void
    {
        $this->skippedIds = array_values(array_filter(
            $this->skippedIds,
            fn ($id) => (int) $id !== $employeeId
        ));

        $this->currentEmployeeId = $employeeId;
        $this->readyToPrint = false;
        $this->lastCreatedExpedientId = null;
        unset($this->currentEmployee);
    }

    public function render()
    {
        $cabinets = ArchiveLocation::where('is_active', true)
            ->whereNotNull('cabinet')
            ->select('cabinet', 'archive_name')
            ->distinct()
            ->orderBy('cabinet')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->cabinet,
                'name' => "Gaveta / Archivero {$item->cabinet}",
            ]);

        $drawers = collect();
        if ($this->selectedCabinet) {
            $drawers = ArchiveLocation::where('is_active', true)
                ->where('cabinet', $this->selectedCabinet)
                ->orderBy('drawer')
                ->get()
                ->map(function ($item) {
                    $label = "Cajón {$item->drawer}";
                    $range = strtoupper(trim($item->alpha_range ?? ''));

                    if ($range === 'DIRECTIVOS') {
                        $label .= '  —  [ Directivos ]';
                    } elseif ($range !== '') {
                        $label .= "  —  [ Rango: {$range} ]";
                    }

                    return [
                        'id' => $item->id,
                        'name' => $label,
                    ];
                });
        }

        $deferredSkips = $this->location_id
            ? CensusSkip::with(['employee', 'user'])
                ->where('archive_location_id', $this->location_id)
                ->where('status', 'deferred')
                ->latest()
                ->get()
            : collect();

        return view('livewire.expedients.continuous-create', [
            'cabinets' => $cabinets,
            'drawers' => $drawers,
            'selectedLocation' => $this->location_id ? ArchiveLocation::find($this->location_id) : null,
            'currentEmployee' => $this->currentEmployee,
            'nextEmployee' => $this->nextEmployee,
            'pendingInRange' => $this->pendingCount(),
            'createdInRange' => $this->createdInRangeCount(),
            'deferredSkips' => $deferredSkips,
            'skippedEmployees' => $this->skippedIds
                ? Employee::whereIn('id', $this->skippedIds)->orderBy('last_name')->orderBy('first_name')->get()
                : collect(),
        ]);
    }

    /**
     * Total de pendientes del cajón (sin importar aplazados de la sesión).
     */
    protected function pendingCount(): int
    {
        return $this->scopedQuery()->whereDoesntHave('expedients')->count();
    }

    /**
     * Total de empleados del cajón que ya cuentan con expediente.
     */
    protected function createdInRangeCount(): int
    {
        return $this->scopedQuery()->whereHas('expedients')->count();
    }

    /**
     * Empleados del cajón (con o sin expediente) según su rango alfabético.
     */
    protected function scopedQuery(): Builder
    {
        $query = Employee::query();

        if (! $this->location_id) {
            return $query;
        }

        $location = ArchiveLocation::find($this->location_id);
        $letters = $location ? $this->rangeLetters($location->alpha_range) : null;

        if ($letters !== null) {
            $query->whereIn(DB::raw('UPPER(SUBSTR(last_name, 1, 1))'), $letters);
        }

        return $query;
    }
}
