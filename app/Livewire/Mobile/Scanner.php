<?php

namespace App\Livewire\Mobile;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Services\LoanService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.mobile')]
class Scanner extends Component
{
    use Toast;

    public string $scannedCode = '';
    public ?string $lastScannedCode = null;
    public ?int $expedientId = null;
    public ?Expedient $currentExpedient = null;
    public ?LoanRequest $activeLoan = null;
    public ?LoanRequest $pendingLoan = null;

    /**
     * Transmitir a la PC de escritorio como pistola de código de barras virtual inalámbrica
     */
    public bool $transmitToDesktop = true;
    public ?string $pairingPin = null;

    /**
     * Modos de escaneo:
     * - 'interactive': Muestra la tarjeta del expediente con acciones táctiles directas.
     * - 'auto-return': Devuelve en ráfaga automáticamente al escanear (ideal para archivar pilas de expedientes).
     * - 'inquiry': Modo consulta rápida sin realizar alteraciones.
     */
    public string $scannerMode = 'interactive';

    public ?string $statusMessage = null;
    public ?string $statusType = null; // 'success', 'error', 'info', 'warning'
    public int $scansCount = 0;
    public int $autoReturnsCount = 0;
    public bool $soundEnabled = true;
    public bool $vibrateEnabled = true;

    /** @var array<int, array{code: string, name: string, status: string, time: string, message: string, success: bool}> */
    public array $scanHistory = [];

    public ?int $targetLocationId = null;
    public string $returnNotes = '';

    /** @var Collection<int, ArchiveLocation> */
    public Collection $locations;

    public function mount(): void
    {
        $userId = Auth::id() ?? 1;
        $this->pairingPin = request()->query('pin') ?? str_pad((string) (($userId * 73 + 1204) % 10000), 4, '0', STR_PAD_LEFT);
        $this->locations = ArchiveLocation::where('is_active', true)->orderBy('archive_name')->get();
    }

    public function getIsOperatorProperty(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('operator') && ! $user->hasAnyRole(['admin', 'superuser']);
    }

    public function getIsEncargadoProperty(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'superuser']);
    }

    /**
     * Procesa un código detectado por la cámara del teléfono o pistola.
     */
    public function processCode(string $code, LoanService $loanService): void
    {
        $code = trim($code);
        if (empty($code)) {
            return;
        }

        $this->scannedCode = $code;
        $this->lastScannedCode = $code;
        $this->scansCount++;

        // Transmitir inmediatamente a la PC de escritorio como pistola remota si está activado
        if ($this->transmitToDesktop) {
            $userId = Auth::id();
            $userName = Auth::user()?->name ?? 'Celular';
            $now = microtime(true);

            Cache::put('scanner_gun_latest', [
                'code' => $code,
                'user_id' => $userId,
                'user_name' => $userName,
                'time' => $now,
            ], now()->addSeconds(30));

            if ($userId) {
                Cache::put("scanner_gun_user_{$userId}", $code, now()->addSeconds(30));
            }
            if ($this->pairingPin) {
                Cache::put("scanner_gun_pin_{$this->pairingPin}", $code, now()->addSeconds(30));
            }
        }

        // Buscar por código exacto de expediente o por RFC / No. Empleado
        $expedient = Expedient::with(['employee', 'currentLocation', 'loans.requester'])
            ->where('expedient_code', $code)
            ->orWhereHas('employee', function ($q) use ($code) {
                $q->where('rfc', $code)
                    ->orWhere('employee_number', $code);
            })
            ->first();

        if (! $expedient) {
            $this->currentExpedient = null;
            $this->expedientId = null;
            $this->activeLoan = null;
            $this->pendingLoan = null;
            $this->statusType = 'error';
            $this->statusMessage = "Código no encontrado: {$code}";

            $this->recordHistory($code, 'Desconocido', 'Error', $this->statusMessage, false);

            $this->dispatch('scan-error', [
                'message' => $this->statusMessage,
                'sound' => $this->soundEnabled,
                'vibrate' => $this->vibrateEnabled,
            ]);

            return;
        }

        $this->currentExpedient = $expedient;
        $this->expedientId = $expedient->id;
        $this->targetLocationId = $expedient->current_location_id;

        $this->activeLoan = $expedient->loans
            ->where('status', LoanStatus::Delivered)
            ->sortByDesc('created_at')
            ->first();

        $this->pendingLoan = $expedient->loans
            ->whereIn('status', [LoanStatus::Approved, LoanStatus::Pending])
            ->sortByDesc('created_at')
            ->first();

        // MODO AUTO-DEVOLUCIÓN EN RÁFAGA
        if ($this->scannerMode === 'auto-return') {
            $this->executeAutoReturn($expedient, $loanService);

            return;
        }

        // MODO CONSULTA / INTERACTIVO
        $this->statusType = 'success';
        $locationLabel = $expedient->currentLocation?->short_label ?? 'Sin ubicación';
        $employeeName = $expedient->employee?->full_name ?? 'Sin titular';

        $this->statusMessage = "{$expedient->expedient_code} — {$employeeName} [{$locationLabel}]";
        $this->recordHistory($expedient->expedient_code, $employeeName, $expedient->current_status->label(), $this->statusMessage, true);

        $this->dispatch('scan-success', [
            'message' => $this->statusMessage,
            'sound' => $this->soundEnabled,
            'vibrate' => $this->vibrateEnabled,
            'autoNext' => false,
        ]);
    }

    protected function executeAutoReturn(Expedient $expedient, LoanService $loanService): void
    {
        $employeeName = $expedient->employee?->full_name ?? 'Sin titular';

        if ($expedient->current_status === ExpedientStatus::Loaned && $this->activeLoan) {
            try {
                if ($this->isOperator) {
                    $loanService->rearchiveExpedient($expedient);
                    $msg = "✓ {$expedient->expedient_code} devuelto y guardado en gaveta oficial";
                } else {
                    $loanService->returnLoan($this->activeLoan, 'Devolución automática registrada en Escáner Móvil PWA');
                    $msg = "✓ {$expedient->expedient_code} recibido en mostrador (Préstamo cerrado)";
                }

                $this->autoReturnsCount++;
                $this->statusType = 'success';
                $this->statusMessage = $msg;
                $this->recordHistory($expedient->expedient_code, $employeeName, 'Devuelto', $msg, true);

                $this->dispatch('scan-success', [
                    'message' => $msg,
                    'sound' => $this->soundEnabled,
                    'vibrate' => $this->vibrateEnabled,
                    'autoNext' => true,
                ]);
            } catch (\Throwable $e) {
                $this->statusType = 'error';
                $this->statusMessage = 'Error al auto-devolver: '.$e->getMessage();
                $this->dispatch('scan-error', [
                    'message' => $this->statusMessage,
                    'sound' => $this->soundEnabled,
                    'vibrate' => $this->vibrateEnabled,
                ]);
            }
        } elseif ($expedient->current_status === ExpedientStatus::Returned && $this->isOperator) {
            try {
                $loanService->rearchiveExpedient($expedient);
                $msg = "✓ {$expedient->expedient_code} reingresado a gaveta oficial";
                $this->autoReturnsCount++;
                $this->statusType = 'success';
                $this->statusMessage = $msg;
                $this->recordHistory($expedient->expedient_code, $employeeName, 'Disponible', $msg, true);

                $this->dispatch('scan-success', [
                    'message' => $msg,
                    'sound' => $this->soundEnabled,
                    'vibrate' => $this->vibrateEnabled,
                    'autoNext' => true,
                ]);
            } catch (\Throwable $e) {
                $this->statusType = 'error';
                $this->statusMessage = 'Error al archivar: '.$e->getMessage();
                $this->dispatch('scan-error', ['message' => $this->statusMessage, 'sound' => $this->soundEnabled, 'vibrate' => $this->vibrateEnabled]);
            }
        } else {
            $msg = "ℹ {$expedient->expedient_code} ya está en estante ({$expedient->current_status->label()})";
            $this->statusType = 'info';
            $this->statusMessage = $msg;
            $this->recordHistory($expedient->expedient_code, $employeeName, $expedient->current_status->label(), $msg, true);

            $this->dispatch('scan-success', [
                'message' => $msg,
                'sound' => $this->soundEnabled,
                'vibrate' => $this->vibrateEnabled,
                'autoNext' => true,
            ]);
        }
    }

    /**
     * Acción manual de recepción en mostrador (Encargado)
     */
    public function receiveReturn(LoanService $loanService): void
    {
        if (! Auth::user()->can('loans.return')) {
            $this->error('No tienes permisos para registrar devoluciones.');

            return;
        }

        if (! $this->activeLoan || ! $this->currentExpedient) {
            $this->error('No hay préstamo activo para devolver.');

            return;
        }

        try {
            $loanService->returnLoan($this->activeLoan, $this->returnNotes ?: 'Devolución recibida en Escáner Móvil');
            $this->statusType = 'success';
            $this->statusMessage = "Devolución registrada para {$this->currentExpedient->expedient_code}";
            $this->success($this->statusMessage);
            $this->currentExpedient->refresh();
            $this->activeLoan = null;

            $this->dispatch('scan-success', ['message' => $this->statusMessage, 'sound' => $this->soundEnabled, 'vibrate' => $this->vibrateEnabled, 'autoNext' => true]);
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    /**
     * Acción manual de guardado en gaveta (Operador)
     */
    public function storeInDrawer(LoanService $loanService): void
    {
        if (! $this->currentExpedient) {
            return;
        }

        try {
            $loanService->rearchiveExpedient($this->currentExpedient);
            $this->statusType = 'success';
            $this->statusMessage = "Expediente {$this->currentExpedient->expedient_code} archivado en gaveta oficial";
            $this->success($this->statusMessage);
            $this->currentExpedient->refresh();

            $this->dispatch('scan-success', ['message' => $this->statusMessage, 'sound' => $this->soundEnabled, 'vibrate' => $this->vibrateEnabled, 'autoNext' => true]);
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    /**
     * Entrega de expediente
     */
    public function quickDeliver(LoanService $loanService): void
    {
        if (! Auth::user()->can('loans.deliver') || ! $this->pendingLoan || ! $this->currentExpedient) {
            $this->error('No hay solicitud autorizada para entregar.');

            return;
        }

        try {
            $loanService->deliverLoan($this->pendingLoan, Auth::id());
            $this->statusType = 'success';
            $this->statusMessage = "Expediente {$this->currentExpedient->expedient_code} entregado a {$this->pendingLoan->requester?->name}";
            $this->success($this->statusMessage);
            $this->currentExpedient->refresh();
            $this->pendingLoan = null;

            $this->dispatch('scan-success', ['message' => $this->statusMessage, 'sound' => $this->soundEnabled, 'vibrate' => $this->vibrateEnabled, 'autoNext' => true]);
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    /**
     * Reubicación rápida
     */
    public function quickRelocate(LoanService $loanService): void
    {
        if (! Auth::user()->can('expedients.change-location') || ! $this->currentExpedient || ! $this->targetLocationId) {
            return;
        }

        try {
            $loanService->relocateExpedient($this->currentExpedient, $this->targetLocationId);
            $this->currentExpedient->refresh();
            $locationLabel = $this->currentExpedient->currentLocation?->short_label ?? 'Nueva ubicación';
            $this->statusType = 'success';
            $this->statusMessage = "Reubicado a {$locationLabel}";
            $this->success($this->statusMessage);

            $this->dispatch('scan-success', ['message' => $this->statusMessage, 'sound' => $this->soundEnabled, 'vibrate' => $this->vibrateEnabled, 'autoNext' => true]);
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    public function clearCurrent(): void
    {
        $this->currentExpedient = null;
        $this->activeLoan = null;
        $this->pendingLoan = null;
        $this->statusMessage = null;
        $this->statusType = null;
        $this->dispatch('resume-scanner');
    }

    public function setScannerMode(string $mode): void
    {
        $this->scannerMode = $mode;
        $this->clearCurrent();
    }

    public function toggleSound(): void
    {
        $this->soundEnabled = ! $this->soundEnabled;
    }

    public function toggleVibrate(): void
    {
        $this->vibrateEnabled = ! $this->vibrateEnabled;
    }

    protected function recordHistory(string $code, string $name, string $status, string $message, bool $success): void
    {
        array_unshift($this->scanHistory, [
            'code' => $code,
            'name' => $name,
            'status' => $status,
            'time' => now()->format('H:i:s'),
            'message' => $message,
            'success' => $success,
        ]);

        if (count($this->scanHistory) > 15) {
            array_pop($this->scanHistory);
        }
    }

    public function render(): View
    {
        return view('livewire.mobile.scanner');
    }
}
