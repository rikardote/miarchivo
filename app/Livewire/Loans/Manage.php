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

    public bool $requestAgainModalOpen = false;
    public string $requestObservations = '';

    public function mount(LoanRequest $loan)
    {
        $this->loan = $loan->load(['expedient.employee', 'requester', 'approver']);
    }

    public function openRequestAgainModal()
    {
        abort_unless(auth()->user()->can('loans.create'), 403);
        $this->requestObservations = '';
        $this->requestAgainModalOpen = true;
    }

    public function submitRequestAgain()
    {
        abort_unless(auth()->user()->can('loans.create'), 403);

        if (!$this->loan->expedient) {
            $this->error('El expediente ya no existe.');
            return;
        }

        if ($this->loan->expedient->current_status !== \App\Enums\ExpedientStatus::Available) {
            $this->warning("El expediente actualmente está en estado '{$this->loan->expedient->current_status->label()}'. No está disponible para préstamo.");
            $this->requestAgainModalOpen = false;
            return;
        }

        try {
            $newLoan = app(LoanService::class)->requestLoan(
                $this->loan->expedient,
                $this->requestObservations ?: 'Solicitud generada nuevamente desde el historial.'
            );

            $this->success("¡Solicitud enviada exitosamente!");
            $this->requestAgainModalOpen = false;
            return redirect()->route('loans.manage', $newLoan);
        } catch (\Exception $e) {
            $this->error("Error al solicitar: " . $e->getMessage());
        }
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
                    $this->success('Expediente marcado como En Préstamo.');
                    break;
                case 'return':
                    $service->returnLoan($this->loan, $this->notes);
                    $this->success('Expediente devuelto y reingresado al archivo.');
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
