<div>
    <x-page-header title="Usuarios" subtitle="Gestión de accesos y roles del sistema" icon="o-users" class="mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary shadow-2xl shadow-primary/20 rounded-2xl h-14 px-8 font-black uppercase text-xs tracking-widest border-none hover:scale-105 transition-premium" wire:click="createUser">Nuevo Usuario</x-mary-button>
        </x-slot:actions>
    </x-page-header>

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
                ['key' => 'name', 'label' => 'Nombre', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'email', 'label' => 'Correo Electrónico', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
                ['key' => 'roles', 'label' => 'Roles / Nivel Acceso', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
                ['key' => 'custody', 'label' => 'Custodia de Carpetas', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
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
                    <div class="flex flex-wrap gap-1.5 py-2">
                        @foreach($user->roles as $role)
                            @php $meta = $this->getRoleMeta($role->name, $role->permissions->count()); @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border shadow-2xs {{ $meta['badge_color'] }}">
                                <x-mary-icon :name="$meta['icon']" class="w-3.5 h-3.5 shrink-0" />
                                <span>{{ $meta['title'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endscope

                @scope('cell_custody', $user)
                    @if($user->held_expedients_count > 0)
                        <button wire:click="showCustody({{ $user->id }})" class="badge badge-primary gap-1.5 py-2.5 px-3 text-[10px] font-black uppercase tracking-wider shadow-sm hover:scale-105 transition-all cursor-pointer">
                            <x-mary-icon name="o-folder-open" class="w-3.5 h-3.5" />
                            {{ $user->held_expedients_count }} {{ $user->held_expedients_count === 1 ? 'carpeta' : 'carpetas' }}
                        </button>
                    @else
                        <span class="badge badge-ghost text-[9px] font-bold text-slate-400">0 en posesión</span>
                    @endif
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
    <x-mary-modal wire:model="userModal" class="p-6 sm:p-8" box-class="max-w-2xl w-full">
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center font-black shadow-sm">
                        <x-mary-icon name="{{ $editingUser ? 'o-user-circle' : 'o-user-plus' }}" class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                            {{ $editingUser ? 'Editar Usuario' : 'Nuevo Usuario' }}
                        </h3>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                            Credenciales de acceso y asignación de roles
                        </p>
                    </div>
                </div>
            </div>

            <x-mary-form wire:submit="saveUser" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-mary-input label="Nombre Completo" wire:model="name" icon="o-user" placeholder="Ej. Juan Pérez González" class="rounded-xl h-12" />
                    </div>
                    <x-mary-input label="Correo Institucional" wire:model="email" icon="o-envelope" placeholder="usuario@correo.gob.mx" class="rounded-xl h-12" />
                    <x-mary-input label="Contraseña" wire:model="password" type="password" icon="o-key" placeholder="••••••••" hint="{{ $editingUser ? 'Dejar en blanco para mantener actual' : 'Mínimo 8 caracteres' }}" class="rounded-xl h-12" />
                </div>
                
                {{-- Sección de Roles --}}
                <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-white/10">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <x-mary-icon name="o-shield-check" class="w-4 h-4 text-primary" />
                                <span>Roles y Niveles de Acceso</span>
                            </label>
                            <p class="text-[11px] text-slate-400">
                                Selecciona uno o varios perfiles operativos asignados a este usuario.
                            </p>
                        </div>
                        <button 
                            type="button"
                            wire:click="openNewRoleModal" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 transition-colors self-start sm:self-auto cursor-pointer"
                        >
                            <x-mary-icon name="o-plus" class="w-3.5 h-3.5" />
                            <span>Definir Nuevo Rol</span>
                        </button>
                    </div>

                    @error('selectedRoles')
                        <p class="text-xs font-bold text-rose-500">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[280px] overflow-y-auto pr-1">
                        @foreach($roles as $role)
                            @php
                                $meta = $this->getRoleMeta($role->name, $role->permissions->count());
                                $isSelected = in_array($role->name, $selectedRoles);
                            @endphp
                            <div 
                                wire:click="toggleRole('{{ $role->name }}')"
                                class="relative cursor-pointer select-none rounded-xl border p-3.5 transition-all duration-200 flex flex-col justify-between gap-2 {{ $isSelected ? 'border-primary/60 bg-primary/5 shadow-xs ring-1 ring-primary/30' : 'border-slate-200 dark:border-white/10 bg-slate-50/50 dark:bg-white/5 hover:border-slate-300 dark:hover:border-white/20' }}"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-primary text-white shadow-xs' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                            <x-mary-icon :name="$meta['icon']" class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-900 dark:text-white leading-tight">
                                                {{ $meta['title'] }}
                                            </div>
                                            <span class="font-mono text-[10px] text-slate-400">
                                                {{ $role->name }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="shrink-0 pt-0.5">
                                        <input 
                                            type="checkbox" 
                                            class="checkbox checkbox-primary checkbox-xs rounded" 
                                            @checked($isSelected)
                                            tabindex="-1"
                                            readonly
                                        />
                                    </div>
                                </div>

                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug line-clamp-2">
                                    {{ $meta['description'] }}
                                </p>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/5 text-[10px]">
                                    <span class="text-slate-400 font-semibold">Permisos:</span>
                                    <span class="font-mono font-bold text-slate-600 dark:text-slate-300 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800">
                                        {{ $role->permissions->count() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot:actions>
                    <div class="flex items-center justify-end gap-3 w-full pt-4 border-t border-slate-100 dark:border-white/10">
                        <x-mary-button label="Cancelar" @click="$wire.userModal = false" class="btn-ghost rounded-xl px-5" />
                        <x-mary-button label="Guardar Usuario" type="submit" class="btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/20" spinner="saveUser" />
                    </div>
                </x-slot:actions>
            </x-mary-form>
        </div>
    </x-mary-modal>

    <!-- Modal para Definir Nuevo Rol -->
    <x-mary-modal wire:model="newRoleModal" class="p-6 sm:p-8" box-class="max-w-2xl w-full">
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-black">
                        <x-mary-icon name="o-key" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">
                            Definir Nuevo Rol
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            Crea un perfil de acceso y asigna sus permisos granulares
                        </p>
                    </div>
                </div>
            </div>

            <x-mary-form wire:submit="saveNewRole" class="space-y-4">
                <x-mary-input 
                    label="Identificador del Rol (Slug único)" 
                    wire:model="newRoleName" 
                    icon="o-identification" 
                    placeholder="ej. gestor_digital, supervisor" 
                    hint="Solo letras, números, guiones y guiones bajos (sin espacios)" 
                    class="rounded-xl h-12"
                />

                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Permisos Disponibles por Módulo
                        </label>
                        <span class="text-[11px] font-mono text-slate-400">
                            {{ count($newRolePermissions) }} seleccionado(s)
                        </span>
                    </div>

                    <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
                        @foreach($availablePermissions as $group => $perms)
                            <div class="rounded-xl border border-slate-200 dark:border-white/10 p-3 bg-slate-50/50 dark:bg-white/5">
                                <div class="text-[11px] font-black text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-1.5 uppercase tracking-wider">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <span>{{ $group }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/5 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <input 
                                                type="checkbox" 
                                                wire:model="newRolePermissions" 
                                                value="{{ $perm->name }}" 
                                                class="checkbox checkbox-primary checkbox-xs rounded"
                                            />
                                            <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300">
                                                {{ $perm->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot:actions>
                    <div class="flex items-center justify-end gap-3 w-full pt-4 border-t border-slate-100 dark:border-white/10">
                        <x-mary-button label="Cancelar" @click="$wire.newRoleModal = false" class="btn-ghost rounded-xl px-5" />
                        <x-mary-button label="Crear Rol" type="submit" class="btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/20" spinner="saveNewRole" />
                    </div>
                </x-slot:actions>
            </x-mary-form>
        </div>
    </x-mary-modal>

    <!-- Modal de Expedientes en Custodia por Usuario -->
    <x-mary-modal wire:model="custodyModal" class="p-6 sm:p-8 modal-wide">
        @if($custodyUser)
            <div class="space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center font-black text-lg shadow-lg shadow-primary/20">
                            {{ strtoupper(substr($custodyUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-primary">Expedientes en Posesión</span>
                                <span class="badge badge-neutral badge-sm font-mono text-[10px]">{{ count($custodyLoans) }} en préstamo</span>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight uppercase">{{ $custodyUser->name }}</h3>
                            <p class="text-xs text-slate-400 font-medium">{{ $custodyUser->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                    @forelse($custodyLoans as $loan)
                        @php
                            $isOverdue = $loan->due_date && $loan->due_date->isPast();
                            $daysDiff = $loan->due_date ? abs((int) now()->diffInDays($loan->due_date, false)) : null;
                        @endphp
                        <div class="p-4 rounded-2xl border {{ $isOverdue ? 'border-rose-200 bg-rose-50/50 dark:border-rose-900/30 dark:bg-rose-950/20' : 'border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                        {{ $loan->expedient->expedient_code }}
                                    </span>
                                    <span class="badge badge-neutral badge-sm font-mono text-[9px] uppercase">
                                        Tomo {{ $loan->expedient->volume_number }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="badge badge-error badge-sm font-black uppercase text-[9px]">
                                            ¡Vencido hace {{ $daysDiff }} día(s)!
                                        </span>
                                    @elseif($daysDiff !== null)
                                        <span class="badge badge-success badge-sm font-bold uppercase text-[9px]">
                                            Resta(n) {{ $daysDiff }} día(s)
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase">
                                    {{ $loan->expedient->employee->last_name }}, {{ $loan->expedient->employee->first_name }}
                                </p>
                                <div class="flex flex-wrap items-center gap-x-3 text-[10px] font-bold text-slate-400">
                                    <span>RFC: <strong class="text-slate-600 dark:text-slate-300">{{ $loan->expedient->employee->rfc }}</strong></span>
                                    @if($loan->delivered_at)
                                        <span>• Entregado: {{ $loan->delivered_at->format('d/m/Y') }}</span>
                                    @endif
                                    @if($loan->due_date)
                                        <span>• Vencimiento: <strong class="{{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-slate-600 dark:text-slate-300' }}">{{ $loan->due_date->format('d/m/Y') }}</strong></span>
                                    @endif
                                </div>
                                @if($loan->observations)
                                    <p class="text-xs italic text-slate-500 mt-1">"{{ $loan->observations }}"</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <x-mary-button label="Ver Carpeta" icon="o-eye" link="{{ route('expedients.show', $loan->expedient) }}" class="btn-ghost btn-xs font-bold uppercase" />
                                <x-mary-button label="Gestionar" icon="o-arrow-path" link="{{ route('loans.manage', $loan) }}" class="btn-primary btn-xs font-bold uppercase" />
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400">
                            <x-mary-icon name="o-check-circle" class="w-10 h-10 mx-auto mb-2 text-emerald-500 opacity-60" />
                            <p class="text-xs font-bold">Este usuario no tiene ningún expediente físico en su poder en este momento.</p>
                        </div>
                    @endforelse
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <x-mary-button label="Cerrar" @click="$wire.custodyModal = false" class="btn-ghost rounded-xl px-6" />
                </div>
            </div>
        @endif
    </x-mary-modal>

    @if(session('success'))
        <x-mary-toast />
    @endif
</div>
