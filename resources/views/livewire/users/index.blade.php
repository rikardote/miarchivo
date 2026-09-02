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
                    <div class="flex flex-wrap gap-2 py-2">
                        @foreach($user->roles as $role)
                            <div class="px-3 py-1 rounded-lg bg-primary/5 text-primary text-[9px] font-black uppercase border border-primary/10 shadow-sm">
                                {{ $role->name }}
                            </div>
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
                                    {{ $loan->expedient->employee->first_name }} {{ $loan->expedient->employee->last_name }}
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
