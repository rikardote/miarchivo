<?php

namespace App\Livewire\Loans;

use App\Enums\LoanStatus;
use App\Models\LoanRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = '';

    public string $tab = 'all'; // 'all', 'delivered', 'overdue'

    public ?int $selectedUserId = null;

    public string $search = '';

    public bool $myLoansOnly = false;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    protected $queryString = [
        'myLoansOnly' => ['except' => false, 'as' => 'mine'],
        'tab' => ['except' => 'all'],
        'selectedUserId' => ['except' => null, 'as' => 'user'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        if (request()->has('mine')) {
            $this->myLoansOnly = request()->boolean('mine');
        }
        if (request()->has('tab')) {
            $this->tab = request()->get('tab');
        }
        if (request()->has('user')) {
            $this->selectedUserId = (int) request()->get('user') ?: null;
        }
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedUserId()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['status', 'selectedUserId', 'search']);
        $this->tab = 'all';
        $this->resetPage();
    }

    public function exportActiveLoans()
    {
        $query = $this->buildLoansQuery();
        $this->applySorting($query);
        $loans = $query->get();

        return response()->streamDownload(function () use ($loans) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, [
                'ID Solicitud',
                'Código Expediente',
                'Empleado',
                'RFC Empleado',
                'Solicitante / Custodio',
                'Fecha Solicitud',
                'Fecha Entrega',
                'Fecha Vencimiento',
                'Estatus de Mora / Días Atraso',
                'Estado Operativo',
                'Observaciones Solicitud',
                'Notas de Entrega',
                'Notas de Devolución',
            ]);

            foreach ($loans as $loan) {
                $isOverdue = $loan->due_date && $loan->due_date->isPast() && $loan->status === LoanStatus::Delivered;
                $daysLate = $isOverdue
                    ? (int) abs(now()->diffInDays($loan->due_date, false)).' día(s) de atraso'
                    : 'Al corriente';

                fputcsv($file, [
                    $loan->id,
                    $loan->expedient?->expedient_code ?? 'ELIMINADO',
                    $loan->expedient?->employee ? ($loan->expedient->employee->first_name.' '.$loan->expedient->employee->last_name) : 'N/A',
                    $loan->expedient?->employee?->rfc ?? 'N/A',
                    $loan->requester?->name ?? 'Desconocido',
                    $loan->requested_at ? $loan->requested_at->format('Y-m-d H:i:s') : 'N/A',
                    $loan->delivered_at ? $loan->delivered_at->format('Y-m-d H:i:s') : 'N/A',
                    $loan->due_date ? Carbon::parse($loan->due_date)->format('Y-m-d') : 'N/A',
                    $daysLate,
                    optional($loan->status)->label() ?? 'N/A',
                    $loan->observations ?? '',
                    $loan->delivery_notes ?? '',
                    $loan->return_notes ?? '',
                ]);
            }

            fclose($file);
        }, 'prestamos_'.now()->format('Y-m-d_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function buildLoansQuery(): Builder
    {
        $query = LoanRequest::query()->with(['expedient.employee', 'requester', 'approver']);
        $user = Auth::user();

        if (! $user->can('loans.approve') || $this->myLoansOnly) {
            $query->where('requester_id', Auth::id());
        }

        if ($this->selectedUserId) {
            $query->where('requester_id', $this->selectedUserId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('expedient', function ($eq) {
                    $eq->where('expedient_code', 'like', "%{$this->search}%")
                        ->orWhereHas('employee', function ($empQ) {
                            $empQ->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhere('rfc', 'like', "%{$this->search}%");
                        });
                })->orWhereHas('requester', function ($rq) {
                    $rq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            });
        }

        if ($this->tab === 'overdue') {
            $query->where('status', LoanStatus::Delivered)
                ->whereNotNull('due_date')
                ->where('due_date', '<', now());
        } elseif ($this->tab === 'delivered') {
            $query->where('status', LoanStatus::Delivered);
        } elseif ($this->tab === 'pending') {
            $query->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved]);
        } elseif ($this->status) {
            $query->where('status', $this->status);
        }

        return $query;
    }

    protected function applySorting(Builder $query): void
    {
        $column = $this->sortBy['column'] ?? 'created_at';
        $direction = strtolower($this->sortBy['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($column === 'requester.name') {
            $query->join('users as requesters', 'loan_requests.requester_id', '=', 'requesters.id')
                ->orderBy('requesters.name', $direction)
                ->select('loan_requests.*');
        } elseif ($column === 'expedient.expedient_code') {
            $query->join('expedients', 'loan_requests.expedient_id', '=', 'expedients.id')
                ->orderBy('expedients.expedient_code', $direction)
                ->select('loan_requests.*');
        } elseif (in_array($column, ['requested_at', 'due_date', 'delivered_at', 'status', 'created_at', 'id'])) {
            $query->orderBy("loan_requests.{$column}", $direction);
        } else {
            $query->orderBy('loan_requests.created_at', 'desc');
        }
    }

    public function render()
    {
        $query = $this->buildLoansQuery();
        $this->applySorting($query);

        $baseUserScope = function ($q) {
            if (! Auth::user()->can('loans.approve') || $this->myLoansOnly) {
                $q->where('requester_id', Auth::id());
            }
        };

        $counts = [
            'all' => LoanRequest::query()->where($baseUserScope)->count(),
            'delivered' => LoanRequest::query()->where($baseUserScope)->where('status', LoanStatus::Delivered)->count(),
            'overdue' => LoanRequest::query()->where($baseUserScope)->where('status', LoanStatus::Delivered)->whereNotNull('due_date')->where('due_date', '<', now())->count(),
            'pending' => LoanRequest::query()->where($baseUserScope)->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])->count(),
        ];

        $custodians = User::whereHas('loanRequests', function ($q) use ($baseUserScope) {
            $q->where('status', LoanStatus::Delivered)->where($baseUserScope);
        })
            ->withCount(['loanRequests as active_loans_count' => function ($q) {
                $q->where('status', LoanStatus::Delivered);
            }])
            ->orderBy('name')
            ->get();

        return view('livewire.loans.index', [
            'loans' => $query->paginate(10),
            'counts' => $counts,
            'custodians' => $custodians,
            'statuses' => collect(LoanStatus::cases())->map(fn ($status) => [
                'name' => $status->label(),
                'value' => $status->value,
            ]),
        ]);
    }
}
