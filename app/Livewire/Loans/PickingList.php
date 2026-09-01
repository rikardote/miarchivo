<?php

namespace App\Livewire\Loans;

use App\Enums\LoanStatus;
use App\Models\LoanRequest;
use Livewire\Component;

class PickingList extends Component
{
    public function mount()
    {
        abort_unless(auth()->user()->can('loans.deliver') || auth()->user()->can('loans.approve'), 403);
    }

    public function render()
    {
        $loans = LoanRequest::whereIn('status', [LoanStatus::Pending, LoanStatus::Approved, LoanStatus::Reserved])
            ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester'])
            ->get()
            ->sortBy(function ($loan) {
                return ($loan->expedient?->currentLocation?->archive_name ?? 'Z') . '-' . ($loan->expedient?->currentLocation?->drawer ?? 99);
            });

        return view('livewire.loans.picking-list', [
            'loans' => $loans,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->layout('layouts.print'); // Clean layout without navbar for printing
    }
}
