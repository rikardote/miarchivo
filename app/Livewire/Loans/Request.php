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
    public ?Expedient $preSelectedExpedient = null;
    public string $observations = '';
    public string $searchExpedient = '';

    public function mount(?int $expedient = null)
    {
        abort_unless(auth()->user()->can('loans.create'), 403);

        $this->expedient_id = $expedient;
        
        if ($this->expedient_id) {
            $this->preSelectedExpedient = Expedient::with('employee')->find($this->expedient_id);
        }
    }

    public function search(string $value = '')
    {
        $this->searchExpedient = trim($value);
    }

    public function save(LoanService $loanService)
    {
        $this->validate([
            'expedient_id' => 'required|exists:expedients,id',
            'observations' => 'nullable|string|max:500',
        ]);

        $expedient = Expedient::find($this->expedient_id);

        if (!$expedient || !$expedient->isAvailable()) {
            $this->error('Este expediente no está disponible en este momento.');
            return;
        }

        try {
            $loan = $loanService->requestLoan($expedient, $this->observations);
            $this->success('Solicitud de préstamo enviada correctamente.');
            return redirect()->route('loans.index', ['mine' => 1]);
        } catch (\Exception $e) {
            $this->error('Error al solicitar préstamo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $expedients = collect();

        if (mb_strlen($this->searchExpedient) >= 2) {
            $expedients = Expedient::available()
                ->with('employee')
                ->search($this->searchExpedient)
                ->take(30)
                ->get();
        }

        if ($this->expedient_id && !$expedients->contains('id', $this->expedient_id)) {
            $selected = Expedient::with('employee')->find($this->expedient_id);
            if ($selected) {
                $expedients->prepend($selected);
            }
        }

        return view('livewire.loans.request', [
            'expedients' => $expedients,
        ]);
    }
}
