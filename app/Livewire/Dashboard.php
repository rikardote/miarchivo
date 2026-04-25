<?php

namespace App\Livewire;

use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\Employee;
use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->can('loans.approve');

        if ($isAdmin) {
            $data = [
                'totalExpedients' => Expedient::count(),
                'loanedExpedients' => Expedient::where('current_status', ExpedientStatus::Loaned)->count(),
                'pendingRequests' => LoanRequest::where('status', LoanStatus::Pending)->count(),
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
                'recentActivities' => \Spatie\Activitylog\Models\Activity::latest()->limit(8)->get(),
                'isAdmin' => true
            ];
        } else {
            $data = [
                'myActiveLoans' => LoanRequest::where('requester_id', $user->id)
                    ->where('status', LoanStatus::Delivered)
                    ->count(),
                'myPendingRequests' => LoanRequest::where('requester_id', $user->id)
                    ->where('status', LoanStatus::Pending)
                    ->count(),
                'myOverdueLoans' => LoanRequest::where('requester_id', $user->id)
                    ->where('status', LoanStatus::Delivered)
                    ->where('due_date', '<', now())
                    ->count(),
                'recentActivities' => \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)->latest()->limit(5)->get(),
                'isAdmin' => false
            ];
        }

        return view('livewire.dashboard', $data);
    }
}
