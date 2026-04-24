<div>
    <x-mary-header title="Solicitar Préstamo" subtitle="Pide un expediente al archivo central" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('loans.index') }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="max-w-2xl mx-auto">
        <x-mary-form wire:submit="save">
            
            <x-mary-choices 
                label="Expediente" 
                wire:model="expedient_id" 
                :options="$expedients" 
                option-label="expedient_code" 
                option-sub-label="employee.full_name"
                icon="o-folder" 
                single 
                searchable
                placeholder="Busca un expediente disponible..." 
                hint="Sólo se muestran los expedientes actualmente disponibles en el archivo." />

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
