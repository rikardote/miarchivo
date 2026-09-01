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
    public ?int $user_id = null;
    public ?string $observations = null;

    protected $rules = [
        'items' => 'required|array|min:1',
        'user_id' => 'required|exists:users,id',
        'observations' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->items = \Illuminate\Support\Facades\Cache::get('bulk_items_' . auth()->id(), []);
        $this->user_id = \Illuminate\Support\Facades\Cache::get('bulk_user_' . auth()->id());
        $this->observations = \Illuminate\Support\Facades\Cache::get('bulk_obs_' . auth()->id());
    }

    public function updated($property)
    {
        if ($property === 'user_id' || $property === 'observations') {
            \Illuminate\Support\Facades\Cache::put('bulk_user_' . auth()->id(), $this->user_id, 3600);
            \Illuminate\Support\Facades\Cache::put('bulk_obs_' . auth()->id(), $this->observations, 3600);
        }
    }

    /**
     * Sincronizar items desde el caché (usado por wire:poll)
     */
    public function syncItems()
    {
        $this->items = \Illuminate\Support\Facades\Cache::get('bulk_items_' . auth()->id(), []);
    }

    #[\Livewire\Attributes\On('code-scanned')]
    public function processScan($code = null)
    {
        $code = $code ?? $this->scannedCode;
        $code = trim($code);
        
        if (empty($code)) return;

        // Limpiar y extraer código si es una URL (compatibilidad con QR)
        if (str_contains($code, '/')) {
            $parts = explode('/', rtrim($code, '/'));
            $code = end($parts);
        }

        // Recargar items de caché antes de verificar duplicados
        $this->items = \Illuminate\Support\Facades\Cache::get('bulk_items_' . auth()->id(), []);

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

        \Illuminate\Support\Facades\Cache::put('bulk_items_' . auth()->id(), $this->items, 3600);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        \Illuminate\Support\Facades\Cache::put('bulk_items_' . auth()->id(), $this->items, 3600);
    }

    public function clearList()
    {
        $this->items = [];
        \Illuminate\Support\Facades\Cache::forget('bulk_items_' . auth()->id());
        $this->success("Lista limpiada.");
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
                $loanService->requestLoan($expedient, $this->observations, $this->user_id);
                $count++;
            }

            // Limpiar caché tras éxito
            \Illuminate\Support\Facades\Cache::forget('bulk_items_' . auth()->id());
            \Illuminate\Support\Facades\Cache::forget('bulk_user_' . auth()->id());
            \Illuminate\Support\Facades\Cache::forget('bulk_obs_' . auth()->id());

            $this->success("Se han generado {$count} solicitudes de préstamo exitosamente.");
            $this->redirect(route('loans.index'));

        } catch (\Exception $e) {
            $this->error("Error al procesar: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.loans.bulk-request', [
            'users' => \App\Models\User::all()
        ]);
    }
}
