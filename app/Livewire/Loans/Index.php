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
