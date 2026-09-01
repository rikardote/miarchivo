<?php

namespace App\Livewire\Expedients;

use Livewire\Component;
use Mary\Traits\Toast;

class Scanner extends Component
{
    use Toast;

    public function mount()
    {
        $this->authorize('changeLocation', \App\Models\Expedient::class);
    }

    #[\Livewire\Attributes\On('code-scanned')]
    public function goToExpedient($code)
    {
        
        // Limpiar y extraer código si es una URL
        $code = trim($code);
        if (str_contains($code, '/')) {
            $parts = explode('/', rtrim($code, '/'));
            $code = end($parts);
        }

        $expedient = \App\Models\Expedient::where('expedient_code', $code)->first();

        if (!$expedient) {
            $this->error("Expediente no encontrado: {$code}");
            return;
        }

        return redirect()->route('expedients.show', $expedient);
    }

    public function render()
    {
        return view('livewire.expedients.scanner');
    }
}
