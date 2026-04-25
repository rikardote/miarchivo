<div>
    <x-mary-header title="Ubicaciones Físicas" subtitle="Gestión de archivos, gavetas y cajones">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" wire:click="create">Nueva Ubicación</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por nombre, tipo o sucursal..." />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full">Limpiar</x-mary-button>
            </div>
        </div>

        <x-mary-table :headers="[
            ['key' => 'location_type', 'label' => 'Tipo'],
            ['key' => 'archive_name', 'label' => 'Nombre del Archivo'],
            ['key' => 'branch.name', 'label' => 'Sucursal'],
            ['key' => 'details', 'label' => 'Detalles'],
            ['key' => 'is_active', 'label' => 'Estado'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-1']
        ]" :rows="$locations" :sort-by="$sortBy" with-pagination>

            @scope('cell_details', $location)
                <div class="flex flex-col text-sm">
                    @if($location->cabinet) <span class="text-gray-500">Gaveta: {{ $location->cabinet }}</span> @endif
                    @if($location->drawer) <span class="text-gray-500">Cajón: {{ $location->drawer }}</span> @endif
                    @if($location->alpha_range) <span class="text-gray-500">Rango: {{ $location->alpha_range }}</span> @endif
                </div>
            @endscope

            @scope('cell_is_active', $location)
                <x-mary-badge :value="$location->is_active ? 'Activo' : 'Inactivo'" 
                    class="{{ $location->is_active ? 'badge-success' : 'badge-error' }}" />
            @endscope

            @scope('cell_actions', $location)
                <div class="flex space-x-2">
                    <x-mary-button icon="o-pencil" wire:click="edit({{ $location->id }})" class="btn-sm btn-ghost" tooltip="Editar" />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    <!-- Modal Form -->
    <x-mary-modal wire:model="locationModal" title="{{ $editing ? 'Editar Ubicación' : 'Nueva Ubicación' }}" separator>
        <div class="grid grid-cols-1 gap-4">
            <x-mary-select label="Sucursal" wire:model="branch_id" :options="$branches" placeholder="Seleccione sucursal" />
            
            <div class="grid grid-cols-2 gap-4">
                <x-mary-select label="Tipo de Archivo" wire:model="location_type" :options="$types" placeholder="Seleccione tipo" />
                <x-mary-input label="Nombre del Archivo" wire:model="archive_name" placeholder="Ej: Archivo Central" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Gaveta / Mueble" wire:model="cabinet" placeholder="Ej: A-01" />
                <x-mary-input label="Cajón / Nivel" wire:model="drawer" placeholder="Ej: 3" />
            </div>

            <x-mary-input label="Rango Alfabético (Opcional)" wire:model="alpha_range" placeholder="Ej: A-M" />
            
            <x-mary-textarea label="Notas" wire:model="notes" placeholder="Información adicional sobre la ubicación..." rows="3" />

            <x-mary-checkbox label="Esta ubicación está activa" wire:model="is_active" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="$toggle('locationModal')" />
            <x-mary-button label="Guardar" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-mary-modal>
</div>
