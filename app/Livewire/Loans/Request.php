<?php

namespace App\Livewire\Loans;

use App\Models\Expedient;
use App\Services\LoanService;
use Livewire\Component;
use Mary\Traits\Toast;

class Request extends Component
{
    use Toast;

    public ?int $expedient_id = null;
    public string $observations = '';

    public function mount(?int $expedient = null)
    {
        $this->expedient_id = $expedient;
    }

    public function save(LoanService $loanService)
    {
        $this->validate([
            'expedient_id' => 'required|exists:expedients,id',
            'observations' => 'nullable|string|max:500',
        ]);

        $expedient = Expedient::find($this->expedient_id);

        if (!$expedient->isAvailable()) {
            $this->error('Este expediente no está disponible en este momento.');
            return;
        }

        try {
            $loan = $loanService->requestLoan($expedient, $this->observations);
            $this->success('Solicitud de préstamo enviada correctamente.');
            return redirect()->route('loans.index');
        } catch (\Exception $e) {
            $this->error('Error al solicitar préstamo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.loans.request', [
            'expedients' => Expedient::available()->with('employee')->take(100)->get(),
        ]);
    }
}
