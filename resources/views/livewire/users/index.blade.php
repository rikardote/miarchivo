<div>
    <x-mary-header title="Usuarios" subtitle="Gestión de accesos y roles del sistema">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary">Nuevo Usuario</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por nombre o correo..." />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full">Limpiar</x-mary-button>
            </div>
        </div>

        <x-mary-table :headers="[
            ['key' => 'name', 'label' => 'Nombre'],
            ['key' => 'email', 'label' => 'Correo Electrónico'],
            ['key' => 'roles', 'label' => 'Roles'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-1']
        ]" :rows="$users" :sort-by="$sortBy" with-pagination>

            @scope('cell_roles', $user)
                <div class="flex flex-wrap gap-1">
                    @foreach($user->roles as $role)
                        <x-mary-badge :value="$role->name" class="badge-outline badge-primary" />
                    @endforeach
                </div>
            @endscope

            @scope('cell_actions', $user)
                <div class="flex space-x-2">
                    <x-mary-button icon="o-pencil" class="btn-sm btn-ghost" tooltip="Editar" />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
