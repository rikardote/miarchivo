<?php

namespace App\Listeners;

use App\Events\LoanRequested;
use App\Events\LoanApproved;
use App\Events\LoanDelivered;
use App\Events\LoanReturned;

class LoanActivityListener
{
    public function handle(object $event): void
    {
        $loanRequest = $event->loanRequest;
        $expedient = $loanRequest->expedient;
        $employee = $expedient->employee;

        if ($event instanceof LoanRequested) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Solicitud de préstamo creada para el expediente {$expedient->expedient_code} ({$employee->full_name})");

            // Notify Admins and Superusers - TEMPORARILY DISABLED DUE TO DB TABLE ISSUE
            // $admins = \App\Models\User::role(['admin', 'superuser'])->get();
            // \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewLoanRequestNotification($loanRequest));
        }

        if ($event instanceof LoanApproved) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Préstamo aprobado para el expediente {$expedient->expedient_code}");
        }

        if ($event instanceof LoanDelivered) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Expediente {$expedient->expedient_code} entregado a {$employee->full_name}");
        }

        if ($event instanceof LoanReturned) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Expediente {$expedient->expedient_code} devuelto al archivo");
        }
    }
}
