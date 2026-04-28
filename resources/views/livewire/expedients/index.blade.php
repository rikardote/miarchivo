<div>
    <x-mary-header title="Expedientes" subtitle="Gestión y búsqueda de expedientes físicos">
        <x-slot:actions>
            @can('create', \App\Models\Expedient::class)
                <x-mary-button icon="o-plus" class="btn-primary" link="{{ route('expedients.create') }}">Nuevo Expediente</x-mary-button>
            @endcan
        </x-slot:actions>
    </x-mary-header>

    @if(count($selected) > 0)
        <div class="bg-primary/10 border border-primary/20 rounded-xl p-4 mb-6 flex justify-between items-center animate-in fade-in slide-in-from-top-4">
            <div class="flex items-center gap-4">
                <span class="font-bold text-primary">{{ count($selected) }} seleccionados</span>
                <x-mary-button label="Mover a Ubicación" icon="o-map-pin" wire:click="showBulkMove" class="btn-sm btn-primary" />
                <x-mary-button label="Imprimir Etiquetas" icon="o-printer" class="btn-sm btn-ghost" />
            </div>
            <x-mary-button icon="o-x-mark" wire:click="$set('selected', [])" class="btn-sm btn-ghost" />
        </div>
    @endif

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
            <div class="md:col-span-2">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por código, RFC o nombre..." />
            </div>
            <div>
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Estado" />
            </div>
            <div>
                <x-mary-select wire:model.live="branch_id" :options="$branches" option-label="name" option-value="id" placeholder="Sede" />
            </div>
            <div>
                <x-mary-select wire:model.live="department_id" :options="$departments" option-label="name" option-value="id" placeholder="Depto" />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full">Limpiar</x-mary-button>
            </div>
        </div>

        <x-mary-table :headers="[
            ['key' => 'expedient_code', 'label' => 'Código'],
            ['key' => 'employee.full_name', 'label' => 'Empleado'],
            ['key' => 'employee.branch.name', 'label' => 'Sede'],
            ['key' => 'volume_number', 'label' => 'Tomo'],
            ['key' => 'current_status', 'label' => 'Estado'],
            ['key' => 'currentLocation.full_label', 'label' => 'Ubicación'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-1']
        ]" :rows="$expedients" :sort-by="$sortBy" with-pagination selectable wire:model="selected">
            
            @scope('cell_employee.full_name', $expedient)
                <div class="flex flex-col">
                    <span class="font-bold">{{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}</span>
                    <span class="text-xs text-gray-500">{{ $expedient->employee->rfc }}</span>
                </div>
            @endscope

            @scope('cell_current_status', $expedient)
                <x-mary-badge :value="$expedient->current_status->label()" 
                    class="badge-{{ $expedient->current_status->color() }}" />
            @endscope

            @scope('cell_actions', $expedient)
                <div class="flex space-x-2">
                    <x-mary-button icon="o-eye" link="{{ route('expedients.show', $expedient) }}" class="btn-sm btn-ghost" tooltip="Ver detalles" />
                    @can('update', $expedient)
                        <x-mary-button icon="o-pencil" link="{{ route('expedients.edit', $expedient) }}" class="btn-sm btn-ghost" tooltip="Editar" />
                    @endcan
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
    
    <!-- Bulk Move Modal -->
    <x-mary-modal wire:model="bulkMoveModal" title="Mover Selección" subtitle="Cambiar ubicación de {{ count($selected) }} expedientes" separator>
        <div class="space-y-4">
            <x-mary-select 
                label="Nueva Ubicación" 
                wire:model="targetLocationId" 
                :options="$locations" 
                option-label="full_label" 
                placeholder="Seleccione el destino..." 
                icon="o-map-pin" />
            
            <div class="p-4 bg-info/5 rounded-lg border border-info/10 flex gap-3">
                <x-mary-icon name="o-information-circle" class="text-info" />
                <p class="text-xs text-gray-500">Esta acción actualizará la ubicación física de todos los expedientes seleccionados y registrará el movimiento en su historial individual.</p>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="$toggle('bulkMoveModal')" />
            <x-mary-button label="Confirmar Traslado" wire:click="executeBulkMove" class="btn-primary" spinner="executeBulkMove" />
        </x-slot:actions>
    </x-mary-modal>
</div>
