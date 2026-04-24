<div>
    <x-mary-header title="Editar Expediente: {{ $expedient->expedient_code }}" subtitle="Actualizar ubicación y metadatos físicos" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('expedients.show', $expedient) }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="max-w-2xl mx-auto">
        <x-mary-card title="Mover Expediente" class="mb-6 bg-base-100">
            <x-mary-form wire:submit="save">
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">Ubicación Actual:</p>
                    <p class="font-bold">{{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}</p>
                </div>

                <x-mary-select 
                    label="Nueva Ubicación Física" 
                    wire:model="location_id" 
                    :options="$locations" 
                    option-label="full_label" 
                    icon="o-map-pin"
                    hint="Selecciona la nueva caja o estante" />

                <x-mary-textarea 
                    label="Notas de Movimiento (Opcional)" 
                    wire:model="movement_notes" 
                    placeholder="Ej. Reubicado por falta de espacio..."
                    rows="3" />

                <x-slot:actions>
                    <x-mary-button label="Guardar Cambios" icon="o-check" class="btn-primary" type="submit" spinner="save" />
                </x-slot:actions>
            </x-mary-form>
        </x-mary-card>
    </div>
</div>
