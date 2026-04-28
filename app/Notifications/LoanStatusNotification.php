<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanStatusNotification extends Notification
{
    use Queueable;

    protected $loan;
    protected $message;
    protected $type;

    public function __construct($loan, $message, $type = 'info')
    {
        $this->loan = $loan;
        $this->message = $message;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'expedient_code' => $this->loan->expedient->expedient_code ?? 'N/A',
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => route('loans.manage', $this->loan),
        ];
    }
}
