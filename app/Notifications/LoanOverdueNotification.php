<?php

namespace App\Notifications;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public LoanRequest $loanRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expedient = $this->loanRequest->expedient;
        $employee = $expedient->employee;

        return (new MailMessage)
            ->subject('Aviso de Préstamo Vencido: ' . $expedient->expedient_code)
            ->greeting('Hola ' . $notifiable->name)
            ->line('Le informamos que el siguiente expediente que tiene en su poder ha vencido:')
            ->line('**Expediente:** ' . $expedient->expedient_code)
            ->line('**Empleado:** ' . $employee->full_name)
            ->line('**Fecha de Vencimiento:** ' . ($this->loanRequest->due_date ? \Carbon\Carbon::parse($this->loanRequest->due_date)->format('d/m/Y') : 'N/A'))
            ->action('Ver Solicitud', route('loans.index'))
            ->line('Por favor, devuelva el expediente al archivo a la brevedad posible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_request_id' => $this->loanRequest->id,
            'expedient_code' => $this->loanRequest->expedient->expedient_code,
            'message' => 'El préstamo del expediente ' . $this->loanRequest->expedient->expedient_code . ' ha vencido.',
            'type' => 'overdue'
        ];
    }
}
