<div>
    <x-mary-header title="Solicitar Préstamo" subtitle="Pide un expediente al archivo central" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('loans.index') }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="max-w-2xl mx-auto">
        <x-mary-form wire:submit="save">
            
            @if($preSelectedExpedient)
                <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 mb-6 flex items-center gap-6">
                    <div class="w-14 h-14 bg-primary text-white rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                        <x-mary-icon name="o-folder" class="w-8 h-8" />
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em]">Expediente Seleccionado</span>
                            <span class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $preSelectedExpedient->expedient_code }}</span>
                            <span class="text-sm font-bold text-slate-500 mt-1">{{ $preSelectedExpedient->employee->first_name }} {{ $preSelectedExpedient->employee->last_name }}</span>
                        </div>
                    </div>
                    <input type="hidden" wire:model="expedient_id">
                </div>
            @else
                <x-mary-choices 
                    label="Expediente" 
                    wire:model="expedient_id" 
                    :options="$expedients" 
                    option-label="expedient_code" 
                    option-sub-label="employee.full_name"
                    icon="o-folder" 
                    single 
                    searchable
                    search-function="search"
                    no-result-text="No se encontraron expedientes disponibles"
                    min-chars="2"
                    placeholder="Escribe al menos 2 caracteres (código, RFC o nombre)..." 
                    hint="Escribe en el buscador para localizar y seleccionar el expediente que requieres." />
            @endif

            <x-mary-textarea 
                label="Observaciones / Motivo" 
                wire:model="observations" 
                placeholder="Ej. Revisión para auditoría..."
                rows="3" />

            <x-slot:actions>
                <x-mary-button label="Enviar Solicitud" icon="o-paper-airplane" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </div>
</div>
