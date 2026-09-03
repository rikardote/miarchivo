<?php

namespace App\Livewire;

use App\Enums\LoanStatus;
use App\Enums\MovementType;
use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Services\ExpedientService;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class GlobalScannerModal extends Component
{
    use Toast;

    public bool $isOpen = false;

    public string $scannedCode = '';

    public ?int $expedientId = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public ?string $returnNotes = null;

    public bool $directToDrawer = true;

    public ?int $targetLocationId = null;

    public bool $showRelocateForm = false;

    public bool $remoteGunActive = true;

    public ?string $workstationPin = null;

    public ?float $lastReceivedTimestamp = null;

    public function mount(): void
    {
        $userId = Auth::id() ?? 1;
        $this->workstationPin = str_pad((string) (($userId * 73 + 1204) % 10000), 4, '0', STR_PAD_LEFT);
        $this->lastReceivedTimestamp = microtime(true);
    }

    /**
     * Escucha periódica (Polling ligero cada 1s) para recibir códigos transmitidos desde el celular.
     */
    public function checkRemoteGunScans(): void
    {
        if (! $this->remoteGunActive || ! Auth::check()) {
            return;
        }

        // 1. Verificar si hay un escaneo reciente emitido desde el celular en el mostrador
        $latest = Cache::get('scanner_gun_latest');
        if ($latest && is_array($latest) && isset($latest['time']) && $latest['time'] > ($this->lastReceivedTimestamp ?? 0)) {
            $this->lastReceivedTimestamp = $latest['time'];
            $code = trim($latest['code']);
            $sender = $latest['user_name'] ?? 'Celular';

            $this->resetState();
            $this->isOpen = true;
            $this->scannedCode = $code;
            $this->searchExpedient();

            $this->successMessage = "Recibido de {$sender}: {$code}";

            $this->dispatch('desktop-remote-gun-beep', [
                'code' => $code,
                'sender' => $sender,
            ]);

            return;
        }

        // 2. Verificar por canal directo de usuario o PIN específico
        $userId = Auth::id();
        $userKey = "scanner_gun_user_{$userId}";
        $pinKey = "scanner_gun_pin_{$this->workstationPin}";

        $code = Cache::pull($userKey) ?? Cache::pull($pinKey);

        if ($code) {
            $code = trim($code);
            $this->resetState();
            $this->isOpen = true;
            $this->scannedCode = $code;
            $this->searchExpedient();

            $this->dispatch('desktop-remote-gun-beep', ['code' => $code]);
        }
    }

    #[On('open-global-scanner')]
    public function openScanner(?string $code = null): void
    {
        $this->resetState();
        $this->isOpen = true;

        if ($code !== null && trim($code) !== '') {
            $this->scannedCode = trim($code);
            $this->searchExpedient();
        }
    }

    public function searchScannedCode(string $code): void
    {
        $this->scannedCode = trim($code);
        $this->searchExpedient();
    }

    public function getIsOperatorProperty(): bool
    {
        return Auth::check() && Auth::user()->hasRole('operator') && ! Auth::user()->hasAnyRole(['admin', 'superuser']);
    }

    public function getIsEncargadoProperty(): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole(['admin', 'superuser']);
    }

    public function closeScanner(): void
    {
        $this->isOpen = false;
        $this->resetState();
    }

    public function resetState(): void
    {
        $this->scannedCode = '';
        $this->expedientId = null;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->returnNotes = null;
        $this->directToDrawer = true;
        $this->targetLocationId = null;
        $this->showRelocateForm = false;
    }

    public function searchExpedient(): void
    {
        $code = trim($this->scannedCode);
        if ($code === '') {
            $this->errorMessage = 'Por favor ingresa o escanea un código.';
            $this->expedientId = null;

            return;
        }

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->showRelocateForm = false;

        $expedient = Expedient::query()
            ->with(['employee.branch', 'currentLocation.branch'])
            ->where('expedient_code', $code)
            ->orWhere('id', $code)
            ->orWhereHas('employee', fn ($q) => $q->where('rfc', $code))
            ->first();

        if (! $expedient) {
            $this->expedientId = null;
            $this->errorMessage = "No se encontró ningún expediente con el código o RFC '{$code}'.";

            return;
        }

        $this->expedientId = $expedient->id;
        $this->targetLocationId = $expedient->current_location_id;
    }

    /**
     * Acción del Encargado: Registrar la recepción física del expediente devuelto por el solicitante.
     * Cierra el préstamo, libera la responsabilidad del usuario y deja el expediente como 'Devuelto'
     * en custodia de mostrador, listo para que el archivo lo guarde en su gaveta.
     */
    public function receiveReturn(LoanService $loanService): void
    {
        if (! Auth::user()->can('loans.return')) {
            $this->error('No tienes permisos para registrar devoluciones de préstamos.');

            return;
        }

        $expedient = Expedient::with(['loans', 'currentLocation'])->find($this->expedientId);
        if (! $expedient) {
            $this->errorMessage = 'Expediente no encontrado.';

            return;
        }

        $activeLoan = $expedient->loans()
            ->where('status', LoanStatus::Delivered)
            ->latest()
            ->first();

        if (! $activeLoan) {
            $this->errorMessage = 'No se encontró un préstamo activo para este expediente.';

            return;
        }

        try {
            $loanService->returnLoan($activeLoan, $this->returnNotes);
            $requesterName = $activeLoan->requester?->name ?? 'el solicitante';
            $this->successMessage = "¡Devolución registrada exitosamente! Se liberó la custodia de {$requesterName}.";
            $this->success($this->successMessage);
            $this->dispatch('loan-updated');
            $this->dispatch('expedient-updated');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al registrar la devolución: '.$e->getMessage();
        }
    }

    /**
     * Acción del Operador: Guardar físicamente el expediente en su gaveta oficial.
     * Pasa el estatus a 'Disponible' y registra la custodia física en estantería.
     */
    public function storeInDrawer(LoanService $loanService): void
    {
        if (! Auth::user()->can('loans.return') && ! Auth::user()->can('expedients.change-location')) {
            $this->error('No tienes permisos para archivar expedientes.');

            return;
        }

        $expedient = Expedient::with(['loans', 'currentLocation'])->find($this->expedientId);
        if (! $expedient) {
            $this->errorMessage = 'Expediente no encontrado.';

            return;
        }

        try {
            $loanService->rearchiveExpedient($expedient);
            $locationLabel = $expedient->currentLocation?->short_label ?? 'su estante oficial';
            $this->successMessage = "¡Expediente guardado físicamente en {$locationLabel} y marcado como Disponible!";
            $this->success($this->successMessage);
            $this->dispatch('loan-updated');
            $this->dispatch('expedient-updated');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar en gaveta: '.$e->getMessage();
        }
    }

    /**
     * Devolver expediente de forma inmediata (delegando según directToDrawer).
     */
    public function quickReturn(LoanService $loanService): void
    {
        if ($this->directToDrawer) {
            $this->storeInDrawer($loanService);
        } else {
            $this->receiveReturn($loanService);
        }
    }

    /**
     * Entrega rápida de un expediente con solicitud aprobada en espera.
     */
    public function quickDeliver(LoanService $loanService): void
    {
        if (! Auth::user()->can('loans.deliver')) {
            $this->error('No tienes permisos para entregar expedientes en préstamo.');

            return;
        }

        $expedient = Expedient::with(['loans.requester'])->find($this->expedientId);
        if (! $expedient) {
            $this->errorMessage = 'Expediente no encontrado.';

            return;
        }

        $approvedLoan = $expedient->loans()
            ->whereIn('status', [LoanStatus::Approved, LoanStatus::Reserved])
            ->latest()
            ->first();

        if (! $approvedLoan) {
            $this->errorMessage = 'No hay una solicitud aprobada en espera para este expediente.';

            return;
        }

        try {
            $loanService->deliverLoan($approvedLoan);
            $requesterName = $approvedLoan->requester?->name ?? 'el solicitante';
            $this->successMessage = "¡Expediente entregado formalmente a {$requesterName}!";
            $this->success($this->successMessage);
            $this->dispatch('loan-updated');
            $this->dispatch('expedient-updated');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al entregar expediente: '.$e->getMessage();
        }
    }

    /**
     * Reubicar expediente a otro estante/gaveta de forma inmediata.
     */
    public function quickRelocate(ExpedientService $expedientService): void
    {
        if (! Auth::user()->can('expedients.change-location')) {
            $this->error('No tienes permisos para reubicar expedientes.');

            return;
        }

        $expedient = Expedient::find($this->expedientId);
        if (! $expedient || ! $this->targetLocationId) {
            $this->errorMessage = 'Seleccione una ubicación válida.';

            return;
        }

        $newLocation = ArchiveLocation::find($this->targetLocationId);
        if (! $newLocation) {
            $this->errorMessage = 'Ubicación no encontrada.';

            return;
        }

        try {
            $oldLocationId = $expedient->current_location_id;
            $expedient->update(['current_location_id' => $newLocation->id]);

            $userName = Auth::user()?->name ?? 'Personal de archivo';
            $expedientService->recordMovement(
                $expedient,
                MovementType::Relocated,
                $oldLocationId,
                $newLocation->id,
                "Reubicado a través del escáner rápido a {$newLocation->short_label} por {$userName}."
            );

            $this->showRelocateForm = false;
            $this->successMessage = "¡Expediente reubicado a {$newLocation->short_label} exitosamente!";
            $this->success($this->successMessage);
            $this->dispatch('expedient-updated');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al reubicar expediente: '.$e->getMessage();
        }
    }

    public function render()
    {
        $expedient = null;
        $activeLoan = null;
        $approvedLoan = null;

        if ($this->expedientId) {
            $expedient = Expedient::query()
                ->with([
                    'employee.branch',
                    'currentLocation.branch',
                    'loans' => fn ($q) => $q->with('requester')->latest(),
                ])
                ->find($this->expedientId);

            if ($expedient) {
                $activeLoan = $expedient->loans->firstWhere('status', LoanStatus::Delivered);
                $approvedLoan = $expedient->loans->whereIn('status', [LoanStatus::Approved, LoanStatus::Reserved])->first();
            }
        }

        $locations = $this->showRelocateForm
            ? ArchiveLocation::with('branch')->orderBy('cabinet')->orderBy('drawer')->get()
            : collect();

        return view('livewire.global-scanner-modal', [
            'expedient' => $expedient,
            'activeLoan' => $activeLoan,
            'approvedLoan' => $approvedLoan,
            'locations' => $locations,
        ]);
    }
}
