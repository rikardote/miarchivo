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
        $loans = LoanRequest::whereIn('status', ['delivered', 'approved'])
            ->with(['expedient.employee', 'requester'])
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=prestamos_activos_' . now()->format('Y-m-d') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($loans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Solicitud', 'Expediente', 'Empleado', 'Solicitante', 'Fecha Entrega', 'Fecha Vencimiento', 'Estado']);

            foreach ($loans as $loan) {
                fputcsv($file, [
                    $loan->id,
                    $loan->expedient->expedient_code,
                    $loan->expedient->employee->full_name,
                    $loan->requester->name,
                    $loan->delivered_at?->format('Y-m-d H:i') ?? 'N/A',
                    $loan->due_date?->format('Y-m-d') ?? 'N/A',
                    $loan->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'statuses' => LoanStatus::cases(),
        ]);
    }
}
