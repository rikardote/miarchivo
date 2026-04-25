<?php

namespace App\Notifications;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLoanRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public LoanRequest $loanRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_request_id' => $this->loanRequest->id,
            'requester_name' => $this->loanRequest->requester->name,
            'expedient_code' => $this->loanRequest->expedient->expedient_code,
            'message' => "Nueva solicitud de {$this->loanRequest->requester->name} para el expediente {$this->loanRequest->expedient->expedient_code}",
            'type' => 'new_request'
        ];
    }
}
