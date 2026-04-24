<div>
    <x-mary-header title="Expedientes" subtitle="Gestión y búsqueda de expedientes físicos">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" link="{{ route('expedients.create') }}">Nuevo Expediente</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-2">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por código, RFC o nombre..." />
            </div>
            <div>
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Todos los estados" />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full">Limpiar</x-mary-button>
            </div>
        </div>

        <x-mary-table :headers="[
            ['key' => 'expedient_code', 'label' => 'Código'],
            ['key' => 'employee.full_name', 'label' => 'Empleado'],
            ['key' => 'volume_number', 'label' => 'Tomo'],
            ['key' => 'current_status', 'label' => 'Estado'],
            ['key' => 'currentLocation.full_label', 'label' => 'Ubicación'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-1']
        ]" :rows="$expedients" :sort-by="$sortBy" with-pagination>
            
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
                    <x-mary-button icon="o-pencil" link="{{ route('expedients.edit', $expedient) }}" class="btn-sm btn-ghost" tooltip="Editar" />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
