<div>
    <x-mary-header title="Nuevo Expediente" subtitle="Abrir un expediente físico para un empleado" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('expedients.index') }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="max-w-2xl mx-auto">
        <x-mary-form wire:submit="save">
            
            <x-mary-choices 
                label="Empleado" 
                wire:model="employee_id" 
                :options="$employees" 
                option-label="full_name" 
                option-sub-label="rfc"
                icon="o-user" 
                single 
                searchable
                placeholder="Busca un empleado..." 
                hint="Selecciona al empleado al que pertenece este expediente." />

            <x-mary-select 
                label="Ubicación Física" 
                wire:model="location_id" 
                :options="$locations" 
                option-label="full_label" 
                icon="o-map-pin"
                placeholder="Selecciona la caja o estante..." />

            <x-slot:actions>
                <x-mary-button label="Guardar" icon="o-check" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </div>
</div>
