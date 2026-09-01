<?php

namespace App\Livewire\Loans;

use App\Enums\LoanStatus;
use App\Models\LoanRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = '';
    public bool $myLoansOnly = false;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    protected $queryString = ['myLoansOnly' => ['except' => false, 'as' => 'mine']];

    public function mount()
    {
        if (request()->has('mine')) {
            $this->myLoansOnly = request()->boolean('mine');
        }
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function exportActiveLoans()
    {
        $query = LoanRequest::query()->with(['expedient.employee', 'requester', 'approver']);
        $user = Auth::user();

        if (!$user->can('loans.approve') || $this->myLoansOnly) {
            $query->where('requester_id', Auth::id());
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        $loans = $query->get();

        return response()->streamDownload(function () use ($loans) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para compatibilidad con Microsoft Excel
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, [
                'ID Solicitud',
                'Código Expediente',
                'Empleado',
                'RFC Empleado',
                'Solicitante',
                'Fecha Solicitud',
                'Fecha Entrega',
                'Fecha Vencimiento',
                'Estado Operativo',
                'Observaciones'
            ]);

            foreach ($loans as $loan) {
                fputcsv($file, [
                    $loan->id,
                    $loan->expedient?->expedient_code ?? 'ELIMINADO',
                    $loan->expedient?->employee ? ($loan->expedient->employee->first_name . ' ' . $loan->expedient->employee->last_name) : 'N/A',
                    $loan->expedient?->employee?->rfc ?? 'N/A',
                    $loan->requester?->name ?? 'Desconocido',
                    $loan->requested_at ? $loan->requested_at->format('Y-m-d H:i:s') : 'N/A',
                    $loan->delivered_at ? $loan->delivered_at->format('Y-m-d H:i:s') : 'N/A',
                    $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('Y-m-d') : 'N/A',
                    optional($loan->status)->label() ?? 'N/A',
                    $loan->observations ?? ''
                ]);
            }

            fclose($file);
        }, 'prestamos_' . now()->format('Y-m-d_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $query = LoanRequest::query()->with(['expedient.employee', 'requester', 'approver']);
        $user = Auth::user();

        // If user is not admin, they ALWAYS only see theirs.
        // If they ARE admin but requested 'mine' view, only show theirs.
        if (!$user->can('loans.approve') || $this->myLoansOnly) {
            $query->where('requester_id', Auth::id());
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('livewire.loans.index', [
            'loans' => $query->paginate(10),
            'statuses' => collect(LoanStatus::cases())->map(fn($status) => [
                'name' => $status->label(),
                'value' => $status->value,
            ]),
        ]);
    }
}
