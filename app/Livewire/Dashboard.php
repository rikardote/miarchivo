<?php

namespace App\Livewire;

use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\Employee;
use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'totalExpedients' => Expedient::count(),
            'loanedExpedients' => Expedient::where('current_status', ExpedientStatus::Loaned)->count(),
            'pendingLoans' => LoanRequest::where('status', LoanStatus::Pending)->count(),
            'overdueLoansCount' => LoanRequest::where('status', LoanStatus::Delivered)
                ->where('due_date', '<', now())
                ->count(),
            'totalEmployees' => Employee::count(),
            'branchStats' => \App\Models\Branch::withCount('employees')->get(),
            'statusStats' => collect(ExpedientStatus::cases())->map(fn($status) => [
                'label' => $status->value,
                'count' => Expedient::where('current_status', $status)->count(),
                'color' => match($status) {
                    ExpedientStatus::Available => 'success',
                    ExpedientStatus::Loaned => 'primary',
                    ExpedientStatus::Reserved => 'warning',
                    default => 'neutral'
                }
            ]),
            'recentActivities' => \Spatie\Activitylog\Models\Activity::latest()->limit(5)->get(),
            'overdueLoans' => LoanRequest::where('status', LoanStatus::Delivered)
                ->where('due_date', '<', now())
                ->with(['requester', 'expedient'])
                ->limit(3)
                ->get(),
        ]);
    }
}
