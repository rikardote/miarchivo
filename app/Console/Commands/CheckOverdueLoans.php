<?php

namespace App\Console\Commands;

use App\Models\LoanRequest;
use App\Enums\LoanStatus;
use App\Notifications\LoanOverdueNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckOverdueLoans extends Command
{
    protected $signature = 'loans:check-overdue';
    protected $description = 'Check for overdue loans and notify admins';

    public function handle()
    {
        $this->info('Checking for overdue loans...');

        $overdueLoans = LoanRequest::where('status', LoanStatus::Delivered)
            ->where('due_date', '<', now())
            ->get();

        if ($overdueLoans->isEmpty()) {
            $this->info('No overdue loans found.');
            return;
        }

        $admins = User::role(['admin', 'superuser'])->get();

        foreach ($overdueLoans as $loan) {
            /** @var \App\Models\LoanRequest $loan */
            $this->warn("Loan for expedient {$loan->expedient->expedient_code} is overdue.");
            
            // Notify admins - TEMPORARILY DISABLED
            // Notification::send($admins, new LoanOverdueNotification($loan));
            
            // Log activity
            activity('loans')
                ->performedOn($loan)
                ->log("ALERTA: El préstamo del expediente {$loan->expedient->expedient_code} ha vencido.");
        }

        $this->info('Done. ' . $overdueLoans->count() . ' overdue loans processed.');
    }
}
