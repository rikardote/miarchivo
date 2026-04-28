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

            // Notify Admins and Superusers
            $admins = \App\Models\User::role(['admin', 'superuser'])->get();
            $message = "Nueva solicitud: {$expedient->expedient_code} por {$loanRequest->requester->name}";
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\LoanStatusNotification($loanRequest, $message, 'info'));
            }
        }

        if ($event instanceof LoanApproved) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Préstamo aprobado para el expediente {$expedient->expedient_code}");

            // Notify Requester
            $message = "¡Tu solicitud del expediente {$expedient->expedient_code} ha sido APROBADA!";
            $loanRequest->requester->notify(new \App\Notifications\LoanStatusNotification($loanRequest, $message, 'success'));
        }

        if ($event instanceof LoanDelivered) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Expediente {$expedient->expedient_code} entregado a {$loanRequest->requester->name}");

            // Notify Requester
            $message = "Has recibido físicamente el expediente {$expedient->expedient_code}.";
            $loanRequest->requester->notify(new \App\Notifications\LoanStatusNotification($loanRequest, $message, 'info'));
        }

        if ($event instanceof LoanReturned) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Expediente {$expedient->expedient_code} devuelto al archivo");
        }

        if ($event instanceof \App\Events\LoanCancelled) {
            activity('loans')
                ->performedOn($loanRequest)
                ->log("Solicitud cancelada para el expediente {$expedient->expedient_code}");

            // Notify Requester
            $message = "Tu solicitud del expediente {$expedient->expedient_code} ha sido CANCELADA.";
            $loanRequest->requester->notify(new \App\Notifications\LoanStatusNotification($loanRequest, $message, 'error'));
        }
    }
}
