<div>
    <x-page-header title="Editar Expediente: {{ $expedient->expedient_code }}" subtitle="Actualizar ubicación y metadatos físicos" icon="o-pencil-square" class="mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('expedients.show', $expedient) }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-2xl mx-auto">
        <x-mary-card title="Mover Expediente" class="mb-6 bg-base-100">
            <x-mary-form wire:submit="save">
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">Ubicación Actual:</p>
                    <p class="font-bold">{{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <x-mary-select 
                            label="1. Archivero / Gaveta" 
                            wire:model.live="selectedCabinet" 
                            :options="$cabinets" 
                            placeholder="Selecciona archivero..."
                            icon="o-building-office" />
                    </div>
                    <div>
                        <x-mary-select 
                            label="2. Cajón y Rango" 
                            wire:model="location_id" 
                            :options="$drawers" 
                            placeholder="{{ empty($selectedCabinet) ? 'Primero selecciona un archivero...' : 'Selecciona un cajón...' }}"
                            :disabled="empty($selectedCabinet)"
                            icon="o-inbox-stack" />
                    </div>
                </div>

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
