<div>
    <x-mary-header title="Usuarios" subtitle="Gestión de accesos y roles del sistema" class="mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary shadow-2xl shadow-primary/20 rounded-2xl h-14 px-8 font-black uppercase text-xs tracking-widest border-none hover:scale-105 transition-premium" wire:click="createUser">Nuevo Usuario</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="premium-card p-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 p-2">
            <div class="md:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por nombre o correo..." />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'name', 'label' => 'Nombre', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'email', 'label' => 'Correo Electrónico', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'roles', 'label' => 'Roles / Nivel Acceso', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$users" :sort-by="$sortBy" with-pagination class="table-premium">

                @scope('cell_name', $user)
                    <div class="flex items-center gap-4 pl-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center font-black text-primary border border-slate-200/50 dark:border-white/5 shadow-sm">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <span class="font-black text-slate-900 dark:text-white dark:text-slate-100 tracking-tight">{{ $user->name }}</span>
                    </div>
                @endscope

                @scope('cell_roles', $user)
                    <div class="flex flex-wrap gap-2 py-2">
                        @foreach($user->roles as $role)
                            <div class="px-3 py-1 rounded-lg bg-primary/5 text-primary text-[9px] font-black uppercase border border-primary/10 shadow-sm">
                                {{ $role->name }}
                            </div>
                        @endforeach
                    </div>
                @endscope

                @scope('cell_actions', $user)
                    <div class="flex items-center gap-2 pr-4">
                        <x-mary-button class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Editar" wire:click="editUser({{ $user->id }})">
                            <x-mary-icon name="o-pencil" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>

    <!-- Modal para Usuarios -->
    <x-mary-modal wire:model="userModal" class="p-6">
        <div class="space-y-8 mt-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-xl shadow-primary/30">
                    <x-mary-icon name="o-shield-check" class="w-7 h-7" />
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white dark:text-white tracking-tighter leading-none">{{ $editingUser ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
                    <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-1">Gestión de identidad y permisos</p>
                </div>
            </div>

            <x-mary-form wire:submit="saveUser" class="space-y-6">
                <x-mary-input label="Nombre Completo" wire:model="name" icon="o-user" class="rounded-2xl h-14 px-5 border-slate-100" />
                <x-mary-input label="Correo Institucional" wire:model="email" icon="o-envelope" class="rounded-2xl h-14 px-5 border-slate-100" />
                <x-mary-input label="Contraseña" wire:model="password" type="password" icon="o-key" hint="{{ $editingUser ? 'Dejar en blanco para mantener actual' : 'Mínimo 8 caracteres' }}" class="rounded-2xl h-14 px-5 border-slate-100" />
                
                <div class="p-6 bg-slate-50 dark:bg-white/5 rounded-[1.5rem] border border-slate-100 dark:border-white/5">
                    <x-mary-radio 
                        label="Nivel de Acceso (Rol)" 
                        wire:model="selectedRole" 
                        :options="$roles"
                        option-label="name"
                        option-value="name"
                        class="radio-primary gap-4"
                    />
                </div>

                <x-slot:actions>
                    <div class="flex gap-4 w-full mt-4">
                        <x-mary-button label="Cancelar" @click="$wire.userModal = false" class="btn-ghost rounded-xl" />
                        <x-mary-button label="Guardar Usuario" type="submit" class="btn-primary rounded-xl" spinner="saveUser" />
                    </div>
                </x-slot:actions>
            </x-mary-form>
        </div>
    </x-mary-modal>

    @if(session('success'))
        <x-mary-toast />
    @endif
</div>
