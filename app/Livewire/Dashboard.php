<?php

namespace App\Livewire;

use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\Employee;
use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public bool $showGlossary = false;

    #[On('open-glossary')]
    public function openGlossary()
    {
        $this->showGlossary = true;
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->can('loans.approve');
        $isOperator = $user->hasRole('operator') || (!$isAdmin && $user->can('loans.deliver'));
        $isStaff = $isAdmin || $isOperator;

        if ($isStaff) {
            $statusCounts = Expedient::selectRaw('current_status, count(*) as total')
                ->groupBy('current_status')
                ->pluck('total', 'current_status');

            $data = [
                'totalExpedients' => Expedient::count(),
                'loanedExpedients' => Expedient::whereIn('current_status', [ExpedientStatus::Loaned, ExpedientStatus::Lost])->count(),
                'pendingRequests' => LoanRequest::where('status', LoanStatus::Pending)->count(),
                'overdueLoansCount' => LoanRequest::where('status', LoanStatus::Delivered)
                    ->where('due_date', '<', now())
                    ->count(),
                'overdueLoans' => LoanRequest::with(['expedient.employee', 'requester'])
                    ->where('status', LoanStatus::Delivered)
                    ->where('due_date', '<', now())
                    ->limit(5)
                    ->get(),
                'totalEmployees' => Employee::count(),
                'pendingTransfersCount' => Expedient::whereHas('employee', function($q) {
                    $q->where('employment_status', 'inactive');
                })->whereHas('currentLocation.branch', function($q) {
                    $q->where('code', '!=', 'CEN');
                })->count(),
                'statusStats' => collect(ExpedientStatus::cases())->map(fn($status) => [
                    'label' => $status->label(),
                    'count' => $statusCounts->get($status->value, 0),
                    'color' => $status->color()
                ]),
                'recentActivities' => \Spatie\Activitylog\Models\Activity::with(['subject', 'causer'])->latest()->limit(8)->get()->map(function($activity) {
                    if (in_array($activity->description, ['created', 'updated', 'deleted'])) {
                        $rawType = str_replace('App\\Models\\', '', $activity->subject_type);
                        $subjectType = match($rawType) {
                            'Expedient' => 'Expediente',
                            'LoanRequest' => 'Préstamo',
                            'User' => 'Usuario',
                            'ArchiveLocation' => 'Ubicación',
                            'Employee' => 'Empleado',
                            default => $rawType
                        };
                        
                        $subjectName = $activity->subject ? ($activity->subject->expedient_code ?? $activity->subject->full_name ?? $activity->subject->name ?? "#{$activity->subject_id}") : "#{$activity->subject_id}";
                        
                        $action = match($activity->description) {
                            'created' => 'creó',
                            'updated' => 'actualizó',
                            'deleted' => 'eliminó',
                            default => $activity->description
                        };
                        
                        $activity->description = "Se {$action} el {$subjectType}: {$subjectName}";
                    }
                    return $activity;
                }),
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
                'recentActivities' => \Spatie\Activitylog\Models\Activity::with(['subject', 'causer'])->where('causer_id', $user->id)->latest()->limit(5)->get()->map(function($activity) {
                    if (in_array($activity->description, ['created', 'updated', 'deleted'])) {
                        $rawType = str_replace('App\\Models\\', '', $activity->subject_type);
                        $subjectType = match($rawType) {
                            'Expedient' => 'Expediente',
                            'LoanRequest' => 'Préstamo',
                            'User' => 'Usuario',
                            'ArchiveLocation' => 'Ubicación',
                            'Employee' => 'Empleado',
                            default => $rawType
                        };
                        
                        $subjectName = $activity->subject ? ($activity->subject->expedient_code ?? $activity->subject->full_name ?? $activity->subject->name ?? "#{$activity->subject_id}") : "#{$activity->subject_id}";
                        
                        $action = match($activity->description) {
                            'created' => 'creó',
                            'updated' => 'actualizó',
                            'deleted' => 'eliminó',
                            default => $activity->description
                        };
                        
                        $activity->description = "Se {$action} el {$subjectType}: {$subjectName}";
                    }
                    return $activity;
                }),
                'isAdmin' => false
            ];
        }

        return view('livewire.dashboard', $data);
    }
}
