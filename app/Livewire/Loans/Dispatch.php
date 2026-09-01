<?php

namespace App\Livewire\Loans;

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
                ->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])
                ->latest()
                ->first();

            if (!$loan) {
                $this->warning("El expediente {$expedient->expedient_code} no tiene órdenes de extracción pendientes.");
                $this->scannedCode = '';
                return;
            }

            try {
                $loanService->deliverLoan($loan);
                $this->success("¡Expediente {$expedient->expedient_code} extraído y entregado a {$loan->requester->name}!");
            } catch (\Exception $e) {
                $this->error("Error al despachar: " . $e->getMessage());
            }
        } else {
            // Tab to_return
            $loan = LoanRequest::where('expedient_id', $expedient->id)
                ->where('status', LoanStatus::Delivered)
                ->latest()
                ->first();

            if (!$loan) {
                $this->warning("El expediente {$expedient->expedient_code} no tiene un préstamo activo por devolver.");
                $this->scannedCode = '';
                return;
            }

            try {
                $loanService->returnLoan($loan);
                $locationName = $expedient->currentLocation ? $expedient->currentLocation->full_label : 'Sin ubicación asignada';
                $this->success("¡Devolución registrada! Reubicar en: {$locationName}");
            } catch (\Exception $e) {
                $this->error("Error al procesar devolución: " . $e->getMessage());
            }
        }

        $this->scannedCode = '';
    }

    public function deliverSingle(int $loanId)
    {
        $loan = LoanRequest::find($loanId);
        if (!$loan) return;

        try {
            app(LoanService::class)->deliverLoan($loan);
            $this->success("Expediente {$loan->expedient->expedient_code} entregado a {$loan->requester->name}.");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    public function deliverBulk()
    {
        if (empty($this->selectedLoans)) {
            $this->error("Selecciona al menos un expediente.");
            return;
        }

        $loanService = app(LoanService::class);
        $count = 0;

        foreach ($this->selectedLoans as $loanId) {
            $loan = LoanRequest::find($loanId);
            if ($loan && in_array($loan->status, [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])) {
                try {
                    $loanService->deliverLoan($loan);
                    $count++;
                } catch (\Exception $e) {
                    // Continue with next
                }
            }
        }

        $this->success("Se despacharon {$count} expedientes con éxito.");
        $this->selectedLoans = [];
    }

    public function returnSingle(int $loanId)
    {
        $loan = LoanRequest::find($loanId);
        if (!$loan) return;

        try {
            app(LoanService::class)->returnLoan($loan);
            $locationName = $loan->expedient->currentLocation ? $loan->expedient->currentLocation->full_label : 'Sin ubicación';
            $this->success("Expediente devuelto. Guardar en: {$locationName}");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    public function render()
    {
        $searchTerm = trim($this->search);

        if ($this->tab === 'to_extract') {
            $query = LoanRequest::whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])
                ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester']);

            if (!empty($searchTerm)) {
                $query->whereHas('expedient', fn($q) => $q->search($searchTerm))
                    ->orWhereHas('requester', fn($q) => $q->where('name', 'like', "%{$searchTerm}%"));
            }

            if ($this->selectedLocationId) {
                $query->whereHas('expedient', fn($q) => $q->where('current_location_id', $this->selectedLocationId));
            }

            // Group by location for picking
            $items = $query->orderBy('created_at', 'asc')->paginate(15);
            $totalPending = LoanRequest::whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])->count();
            $totalReturns = LoanRequest::where('status', LoanStatus::Delivered)->count();
        } else {
            $query = LoanRequest::where('status', LoanStatus::Delivered)
                ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester']);

            if (!empty($searchTerm)) {
                $query->whereHas('expedient', fn($q) => $q->search($searchTerm))
                    ->orWhereHas('requester', fn($q) => $q->where('name', 'like', "%{$searchTerm}%"));
            }

            $items = $query->orderBy('due_date', 'asc')->paginate(15);
            $totalPending = LoanRequest::whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])->count();
            $totalReturns = LoanRequest::where('status', LoanStatus::Delivered)->count();
        }

        return view('livewire.loans.dispatch', [
            'items' => $items,
            'totalPending' => $totalPending,
            'totalReturns' => $totalReturns,
            'locations' => ArchiveLocation::with('branch')->orderBy('archive_name')->get(),
        ]);
    }
}
