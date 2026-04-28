<?php

namespace App\Livewire\Loans;

use App\Models\Expedient;
use App\Services\LoanService;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Collection;

class BulkRequest extends Component
{
    use Toast;

    public string $scannedCode = '';
    public array $items = [];
    public ?string $observations = null;

    protected $rules = [
        'items' => 'required|array|min:1',
        'observations' => 'nullable|string|max:255',
    ];

    public function processScan()
    {
        $code = trim($this->scannedCode);
        
        if (empty($code)) return;

        // Verificar si ya está en la lista
        if (collect($this->items)->contains('code', $code)) {
            $this->warning("El código {$code} ya está en la lista.");
            $this->scannedCode = '';
            return;
        }

        $expedient = Expedient::where('expedient_code', $code)->first();

        if (!$expedient) {
            $this->error("No se encontró el expediente: {$code}");
            $this->scannedCode = '';
            return;
        }

        if (!$expedient->isAvailable()) {
            $this->warning("El expediente {$code} no está disponible (Estatus: {$expedient->current_status->label()}).");
            // Aún así lo agregamos pero marcado como inválido para que el usuario decida
            $this->addItem($expedient, false);
        } else {
            $this->addItem($expedient, true);
        }

        $this->scannedCode = '';
    }

    private function addItem(Expedient $expedient, bool $isValid)
    {
        array_unshift($this->items, [
            'id' => $expedient->id,
            'code' => $expedient->expedient_code,
            'employee' => $expedient->employee->full_name ?? 'N/A',
            'status' => $expedient->current_status->label(),
            'status_color' => $expedient->current_status->color(),
            'isValid' => $isValid
        ]);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(LoanService $loanService)
    {
        $this->validate();

        $validItems = collect($this->items)->filter(fn($i) => $i['isValid']);

        if ($validItems->isEmpty()) {
            $this->error("No hay expedientes válidos para procesar.");
            return;
        }

        try {
            $count = 0;
            foreach ($validItems as $item) {
                $expedient = Expedient::find($item['id']);
                $loanService->requestLoan($expedient, $this->observations);
                $count++;
            }

            $this->success("Se han generado {$count} solicitudes de préstamo exitosamente.");
            return redirect()->route('loans.index');

        } catch (\Exception $e) {
            $this->error("Error al procesar: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.loans.bulk-request');
    }
}
