<div>
    <x-mary-header title="Expedientes" subtitle="Gestión y búsqueda de expedientes físicos" class="mb-10">
        <x-slot:actions>
            @can('create', \App\Models\Expedient::class)
                <x-mary-button icon="o-plus" class="btn-primary shadow-2xl shadow-primary/20 rounded-2xl h-14 px-8 font-black uppercase text-xs tracking-widest border-none hover:scale-105 transition-premium" link="{{ route('expedients.create') }}">Nuevo Expediente</x-mary-button>
            @endcan
        </x-slot:actions>
    </x-mary-header>

    @if(count($selected) > 0)
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 mb-10 flex flex-col md:flex-row justify-between items-center animate-in zoom-in-95 duration-500 gap-6">
            <div class="flex items-center gap-6">
                <div class="w-14 h-14 bg-primary text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-sm">
                    {{ count($selected) }}
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-primary uppercase text-[10px] tracking-[0.3em]">Gestión Grupal</span>
                    <span class="text-slate-600 dark:text-slate-300 dark:text-slate-500 text-xs font-bold mt-1">Expedientes seleccionados para acción</span>
                </div>
                <div class="h-10 w-[1px] bg-primary/20 hidden md:block"></div>
                <div class="flex items-center gap-2">
                    @can('changeLocation', \App\Models\Expedient::class)
                        <x-mary-button label="Mover Ubicación" icon="o-map-pin" wire:click="showBulkMove" class="btn-sm btn-primary rounded-lg px-4" />
                    @endcan
                    <x-mary-button label="Etiquetas" icon="o-printer" class="btn-sm btn-ghost rounded-lg px-4 border border-slate-200" />
                </div>
            </div>
            <x-mary-button icon="o-x-mark" wire:click="$set('selected', [])" class="btn-ghost btn-circle hover:bg-rose-500/10 hover:text-rose-500 transition-premium" />
        </div>
    @endif

    <x-mary-card class="premium-card p-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-10 p-2">
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
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'expedient_code', 'label' => 'Código', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'employee.full_name', 'label' => 'Empleado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'employee.branch.name', 'label' => 'Sede', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'volume_number', 'label' => 'Tomo', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'current_status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'currentLocation.full_label', 'label' => 'Ubicación', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$expedients" :sort-by="$sortBy" with-pagination selectable wire:model="selected" class="table-premium">
                
                @scope('cell_expedient_code', $expedient)
                    <div class="flex items-center gap-3 pl-4">
                        <div class="w-1.5 h-6 bg-primary/20 rounded-full"></div>
                        <span class="font-black text-slate-900 dark:text-white dark:text-slate-100 tracking-tighter text-base">{{ $expedient->expedient_code }}</span>
                    </div>
                @endscope

                @scope('cell_employee.full_name', $expedient)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-tight">{{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}</span>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest bg-slate-50 dark:bg-white/5 px-2 py-0.5 rounded-md border border-slate-100 dark:border-white/5">{{ $expedient->employee->rfc }}</span>
                        </div>
                    </div>
                @endscope

                @scope('cell_current_status', $expedient)
                    <div class="px-4 py-1.5 rounded-xl bg-{{ $expedient->current_status->color() }}/10 text-{{ $expedient->current_status->color() }} text-[9px] font-black uppercase text-center w-fit border border-{{ $expedient->current_status->color() }}/20 shadow-sm">
                        {{ $expedient->current_status->label() }}
                    </div>
                @endscope

                @scope('cell_actions', $expedient)
                    <div class="flex items-center gap-2 pr-4">
                        <x-mary-button link="{{ route('expedients.show', $expedient) }}" class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Ver detalles">
                            <x-mary-icon name="o-eye" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                        @can('update', $expedient)
                            <x-mary-button link="{{ route('expedients.edit', $expedient) }}" class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Editar">
                                <x-mary-icon name="o-pencil" class="w-4 h-4 group-hover/btn:scale-110" />
                            </x-mary-button>
                        @endcan
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>
    
    <!-- Bulk Move Modal -->
    <x-mary-modal wire:model="bulkMoveModal" title="Mover Selección" class="p-6">
        <div class="space-y-8 mt-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-xl shadow-primary/30">
                    {{ count($selected) }}
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white dark:text-white tracking-tighter leading-none">Traslado Masivo</h3>
                    <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-1">Definir nueva ubicación física</p>
                </div>
            </div>

            <x-mary-select 
                label="Nueva Ubicación de Destino" 
                wire:model="targetLocationId" 
                :options="$locations" 
                option-label="full_label" 
                placeholder="Seleccione el estante/caja..." 
                icon="o-map-pin" 
                class="rounded-2xl h-14 px-5 border-slate-100 focus:border-primary/40 shadow-sm" />
            
            <div class="p-6 bg-primary/5 rounded-[1.5rem] border border-primary/10 flex gap-6 group">
                <div class="p-3 bg-primary text-white rounded-2xl h-fit transform group-hover:rotate-6 transition-premium shadow-lg shadow-primary/30">
                    <x-mary-icon name="o-information-circle" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-xs font-black text-primary uppercase tracking-[0.2em] mb-2">Protocolo de Movimiento</p>
                    <p class="text-xs text-slate-600 dark:text-slate-300 dark:text-slate-500 leading-relaxed font-medium">Esta acción actualizará la ubicación física de todos los expedientes seleccionados y generará automáticamente un registro de auditoría en el historial individual de cada carpeta.</p>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full mt-6">
                <x-mary-button label="Cancelar" wire:click="$toggle('bulkMoveModal')" class="btn-ghost rounded-xl" />
                <x-mary-button label="Confirmar Traslado" wire:click="executeBulkMove" class="btn-primary rounded-xl" spinner="executeBulkMove" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
