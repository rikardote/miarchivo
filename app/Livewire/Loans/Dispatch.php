<?php

namespace App\Livewire\Loans;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Services\LoanService;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Dispatch extends Component
{
    use WithPagination, Toast;

    public string $tab = 'to_extract'; // 'to_extract' | 'to_return'
    public string $search = '';
    public string $scannedCode = '';
    public ?int $selectedLocationId = null;
    public array $selectedLoans = [];

    protected $listeners = ['code-scanned' => 'onCodeScanned'];

    public function mount()
    {
        abort_unless(auth()->user()->can('loans.deliver') || auth()->user()->can('loans.return'), 403);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTab()
    {
        $this->reset(['selectedLoans', 'search', 'scannedCode', 'selectedLocationId']);
        $this->resetPage();
    }

    public function onCodeScanned(string $code)
    {
        $this->scannedCode = $code;
        $this->processScan();
    }

    public function processScan()
    {
        $code = trim($this->scannedCode);
        if (empty($code)) {
            return;
        }

        $expedient = Expedient::where('expedient_code', $code)
            ->orWhere('barcode', $code)
            ->first();

        if (!$expedient) {
            $this->error("No se encontró ningún expediente con código: {$code}");
            $this->scannedCode = '';
            return;
        }

        $loanService = app(LoanService::class);

        if ($this->tab === 'to_extract') {
            $loan = LoanRequest::where('expedient_id', $expedient->id)
                ->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved])
                ->latest()
                ->first();

            if (!$loan) {
                $this->warning("El expediente {$expedient->expedient_code} no tiene órdenes de extracción pendientes.");
                $this->scannedCode = '';
                return;
            }

            if ($loan->status === LoanStatus::Pending) {
                $this->warning("El expediente {$expedient->expedient_code} aún requiere aprobación por el encargado de RH antes de poder extraerse.");
                $this->scannedCode = '';
                return;
            }

            try {
                $loanService->extractLoan($loan);
                $this->success("¡Expediente {$expedient->expedient_code} extraído y enviado a Recursos Humanos para entrega!");
            } catch (\Exception $e) {
                $this->error("Error al surtir: " . $e->getMessage());
            }
        } else {
            // Tab to_return: Rearchive to drawer
            if ($expedient->current_status === ExpedientStatus::Returned) {
                try {
                    $loanService->rearchiveExpedient($expedient);
                    $locationName = $expedient->currentLocation ? $expedient->currentLocation->full_label : 'su gaveta';
                    $this->success("¡Expediente {$expedient->expedient_code} guardado correctamente en: {$locationName}!");
                } catch (\Exception $e) {
                    $this->error("Error al re-archivar: " . $e->getMessage());
                }
            } elseif ($expedient->current_status === ExpedientStatus::Loaned) {
                // If user returned directly to Planta Baja
                $loan = LoanRequest::where('expedient_id', $expedient->id)
                    ->where('status', LoanStatus::Delivered)
                    ->latest()
                    ->first();

                try {
                    if ($loan) {
                        $loanService->returnLoan($loan);
                    }
                    $loanService->rearchiveExpedient($expedient);
                    $locationName = $expedient->currentLocation ? $expedient->currentLocation->full_label : 'su gaveta';
                    $this->success("¡Devolución recibida y guardado en gaveta: {$locationName}!");
                } catch (\Exception $e) {
                    $this->error("Error al procesar: " . $e->getMessage());
                }
            } else {
                $this->info("El expediente {$expedient->expedient_code} ya está disponible en su gaveta.");
            }
        }

        $this->scannedCode = '';
    }

    public function extractSingle(int $loanId)
    {
        $loan = LoanRequest::find($loanId);
        if (!$loan) return;

        if ($loan->status === LoanStatus::Pending) {
            $this->warning("Esta solicitud aún no ha sido aprobada por el encargado de Recursos Humanos.");
            return;
        }

        try {
            app(LoanService::class)->extractLoan($loan);
            $this->success("Expediente {$loan->expedient->expedient_code} extraído y enviado a RH.");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    public function extractBulk()
    {
        if (empty($this->selectedLoans)) {
            $this->error("Selecciona al menos un expediente.");
            return;
        }

        $loanService = app(LoanService::class);
        $count = 0;
        $pendingSkipped = 0;

        foreach ($this->selectedLoans as $loanId) {
            $loan = LoanRequest::find($loanId);
            if ($loan) {
                if ($loan->status === LoanStatus::Approved) {
                    try {
                        $loanService->extractLoan($loan);
                        $count++;
                    } catch (\Exception $e) {
                        // Continue with next
                    }
                } elseif ($loan->status === LoanStatus::Pending) {
                    $pendingSkipped++;
                }
            }
        }

        if ($count > 0) {
            $this->success("Se marcaron como surtidos {$count} expedientes y enviados a RH.");
        }
        if ($pendingSkipped > 0) {
            $this->warning("Se omitieron {$pendingSkipped} expedientes por estar pendientes de aprobación en RH.");
        }
        $this->selectedLoans = [];
    }

    public function rearchiveSingle(int $loanId)
    {
        $loan = LoanRequest::find($loanId);
        if (!$loan || !$loan->expedient) return;

        try {
            app(LoanService::class)->rearchiveExpedient($loan->expedient);
            $locationName = $loan->expedient->currentLocation ? $loan->expedient->currentLocation->full_label : 'su gaveta';
            $this->success("Expediente {$loan->expedient->expedient_code} guardado en gaveta ({$locationName}).");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    public function rearchiveBulk()
    {
        if (empty($this->selectedLoans)) {
            $this->error("Selecciona al menos un expediente.");
            return;
        }

        $loanService = app(LoanService::class);
        $count = 0;

        foreach ($this->selectedLoans as $loanId) {
            $loan = LoanRequest::find($loanId);
            if ($loan && $loan->expedient && $loan->expedient->current_status === ExpedientStatus::Returned) {
                try {
                    $loanService->rearchiveExpedient($loan->expedient);
                    $count++;
                } catch (\Exception $e) {
                    // Continue with next
                }
            }
        }

        $this->success("Se guardaron {$count} expedientes en su gaveta correspondiente.");
        $this->selectedLoans = [];
    }

    public function render()
    {
        $searchTerm = trim($this->search);

        if ($this->tab === 'to_extract') {
            $query = LoanRequest::whereIn('status', [LoanStatus::Pending, LoanStatus::Approved])
                ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester']);

            if (!empty($searchTerm)) {
                $query->whereHas('expedient', fn($q) => $q->search($searchTerm))
                    ->orWhereHas('requester', fn($q) => $q->where('name', 'like', "%{$searchTerm}%"));
            }

            if ($this->selectedLocationId) {
                $query->whereHas('expedient', fn($q) => $q->where('current_location_id', $this->selectedLocationId));
            }

            $items = $query->orderBy('created_at', 'asc')->paginate(15);
        } else {
            // Tab to_return: Expedients returned to RH that need to be rearchived to drawers in PB
            $query = LoanRequest::where('status', LoanStatus::Returned)
                ->whereHas('expedient', fn($q) => $q->where('current_status', ExpedientStatus::Returned))
                ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester']);

            if (!empty($searchTerm)) {
                $query->whereHas('expedient', fn($q) => $q->search($searchTerm))
                    ->orWhereHas('requester', fn($q) => $q->where('name', 'like', "%{$searchTerm}%"));
            }

            if ($this->selectedLocationId) {
                $query->whereHas('expedient', fn($q) => $q->where('current_location_id', $this->selectedLocationId));
            }

            $items = $query->orderBy('returned_at', 'asc')->paginate(15);
        }

        $totalPending = LoanRequest::whereIn('status', [LoanStatus::Pending, LoanStatus::Approved])->count();
        $totalReturns = Expedient::where('current_status', ExpedientStatus::Returned)->count();

        return view('livewire.loans.dispatch', [
            'items' => $items,
            'totalPending' => $totalPending,
            'totalReturns' => $totalReturns,
            'locations' => ArchiveLocation::with('branch')->orderBy('archive_name')->get(),
        ]);
    }
}
