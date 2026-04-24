<?php

namespace App\Livewire\Loans;

use App\Models\LoanRequest;
use App\Services\LoanService;
use App\Traits\ConfirmsSudo;
use Livewire\Component;
use Mary\Traits\Toast;

class Manage extends Component
{
    use Toast, ConfirmsSudo;

    public LoanRequest $loan;
    public string $notes = '';

    public bool $sudoModalOpen = false;
    public string $pendingAction = '';

    public function mount(LoanRequest $loan)
    {
        $this->loan = $loan->load(['expedient.employee', 'requester', 'approver']);
    }

    public function triggerAction(string $action)
    {
        // Enforce sudo for critical actions like Delivery and Return
        if (in_array($action, ['deliver', 'return'])) {
            $this->pendingAction = $action;
            $this->sudoModalOpen = true;
        } else {
            $this->executeAction($action);
        }
    }

    public function confirmSudoAndExecute()
    {
        if ($this->confirmSudo()) {
            $this->sudoModalOpen = false;
            $this->executeAction($this->pendingAction);
        } else {
            $this->error('Contraseña incorrecta.');
        }
    }

    protected function executeAction(string $action)
    {
        $service = app(LoanService::class);

        try {
            switch ($action) {
                case 'approve':
                    $service->approveLoan($this->loan);
                    $this->success('Préstamo aprobado.');
                    break;
                case 'deliver':
                    $service->deliverLoan($this->loan);
                    $this->success('Expediente entregado físicamente.');
                    break;
                case 'return':
                    $service->returnLoan($this->loan, $this->notes);
                    $this->success('Expediente devuelto al archivo.');
                    break;
                case 'cancel':
                    $service->cancelLoan($this->loan, $this->notes);
                    $this->info('Solicitud de préstamo cancelada.');
                    break;
            }

            $this->loan->refresh();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.loans.manage');
    }
}
