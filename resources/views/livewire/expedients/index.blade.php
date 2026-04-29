<div>
    <x-mary-header title="Expedientes" subtitle="Gestión y búsqueda de expedientes físicos" class="mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-information-circle" class="btn-ghost btn-circle text-primary hover:bg-primary/5 mr-2" wire:click="$set('showGlossary', true)" tooltip="Ver glosario de estados" />
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
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'expedient', 'label' => 'Expediente', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'employee.branch.name', 'label' => 'Sede', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'volume_number', 'label' => 'Tomo', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'current_status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'currentLocation.short_label', 'label' => 'Ubicación', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$expedients" :sort-by="$sortBy" with-pagination selectable wire:model="selected" class="table-premium">
                
                @scope('cell_expedient', $expedient)
                    <div class="flex flex-col py-2 pl-4">
                        <span class="font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-tight">{{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $expedient->expedient_code }}</span>
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
    <x-mary-modal wire:model="bulkMoveModal" class="p-8 modal-wide">
        <div class="flex items-center gap-6 mb-8 border-b border-slate-100 pb-6">
            <div class="w-16 h-16 bg-primary text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-xl shadow-primary/20">
                {{ count($selected) }}
            </div>
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter leading-none mb-1">Mover Selección</h3>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Relocalización masiva de expedientes</p>
            </div>
        </div>

        <div class="space-y-6">
            <x-mary-select label="Nueva Ubicación de Destino" wire:model="targetLocationId" :options="$locations" option-label="full_label" placeholder="Seleccione el nuevo estante..." icon="o-map-pin" class="rounded-2xl h-14 px-5 border-slate-100" />
            
            <x-mary-alert icon="o-information-circle" class="alert-info bg-primary/5 text-primary border-none text-xs font-bold leading-relaxed rounded-2xl">
                Se registrará un movimiento histórico automático para cada uno de los {{ count($selected) }} expedientes seleccionados.
            </x-mary-alert>
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full mt-6">
                <x-mary-button label="Cancelar" wire:click="$toggle('bulkMoveModal')" class="btn-ghost rounded-xl" />
                <x-mary-button label="Confirmar Traslado" wire:click="executeBulkMove" class="btn-primary rounded-xl" spinner="executeBulkMove" />
            </div>
        </x-slot:actions>
    </x-mary-modal>

    <!-- Glosario de Estados -->
    <x-mary-modal wire:model="showGlossary" title="Glosario Operativo" class="p-6">
        <div class="space-y-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-1 h-1 bg-primary rounded-full"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400 dark:text-slate-400">Interpretación de Estatus</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(\App\Enums\ExpedientStatus::cases() as $status)
                    <div class="flex flex-col gap-3 p-6 rounded-[1.5rem] border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-900 transition-premium group">
                        <div class="px-4 py-1.5 rounded-xl bg-{{ $status->color() }}/10 text-{{ $status->color() }} text-[9px] font-black uppercase text-center w-fit border border-{{ $status->color() }}/20 shadow-sm group-hover:scale-105 transition-premium">
                            {{ $status->label() }}
                        </div>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300 dark:text-slate-500 leading-relaxed">
                            @switch($status)
                                @case(\App\Enums\ExpedientStatus::Available) Ubicado físicamente en su estante asignado. @break
                                @case(\App\Enums\ExpedientStatus::Requested) En proceso de validación administrativa. @break
                                @case(\App\Enums\ExpedientStatus::Reserved) Validado y listo para ser recogido. @break
                                @case(\App\Enums\ExpedientStatus::Loaned) En posesión física del usuario solicitante. @break
                                @case(\App\Enums\ExpedientStatus::Returned) Pendiente de re-ubicación en estantería. @break
                                @case(\App\Enums\ExpedientStatus::InStorage) En depósito temporal de baja frecuencia. @break
                                @case(\App\Enums\ExpedientStatus::Archived) Enviado a archivo de concentración final. @break
                                @case(\App\Enums\ExpedientStatus::Lost) Sin localización física confirmada. @break
                            @endswitch
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="ENTENDIDO" wire:click="$set('showGlossary', false)" class="btn-primary w-full rounded-2xl h-14 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/20 border-none" />
        </x-slot:actions>
    </x-mary-modal>
</div>
