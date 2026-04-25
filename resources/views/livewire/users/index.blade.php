<div>
    <x-mary-header title="Usuarios" subtitle="Gestión de accesos y roles del sistema">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" wire:click="createUser">Nuevo Usuario</x-mary-button>
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
                    <x-mary-button icon="o-pencil" class="btn-sm btn-ghost" tooltip="Editar" wire:click="editUser({{ $user->id }})" />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    <!-- Modal para Usuarios -->
    <x-mary-modal wire:model="userModal" title="{{ $editingUser ? 'Editar Usuario' : 'Nuevo Usuario' }}" separator>
        <x-mary-form wire:submit="saveUser">
            <x-mary-input label="Nombre" wire:model="name" icon="o-user" />
            <x-mary-input label="Correo Electrónico" wire:model="email" icon="o-envelope" />
            <x-mary-input label="Contraseña" wire:model="password" type="password" icon="o-key" hint="{{ $editingUser ? 'Dejar en blanco para mantener actual' : '' }}" />
            
            <div class="mt-4">
                <x-mary-radio 
                    label="Nivel de Acceso (Rol)" 
                    wire:model="selectedRole" 
                    :options="$roles"
                    option-label="name"
                    option-value="name"
                    hint="Define el nivel de permisos del usuario"
                />
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancelar" @click="$wire.userModal = false" />
                <x-mary-button label="Guardar" type="submit" icon="o-check" class="btn-primary" spinner="saveUser" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    @if(session('success'))
        <x-mary-toast />
    @endif
</div>
