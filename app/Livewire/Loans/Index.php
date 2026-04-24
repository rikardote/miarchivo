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
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = LoanRequest::query()->with(['expedient.employee', 'requester', 'approver']);

        // Check if user is admin/approver, otherwise only show their own
        if (!Auth::user()->can('loans.approve')) {
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
