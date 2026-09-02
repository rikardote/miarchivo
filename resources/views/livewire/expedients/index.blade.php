<div>
    <x-mary-header title="Expedientes" subtitle="Gestión y búsqueda de expedientes físicos" class="mb-6 sm:mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-information-circle" class="btn-ghost btn-circle btn-sm sm:btn-md text-primary hover:bg-primary/5 mr-1" wire:click="$set('showGlossary', true)" tooltip="Ver glosario de estados" />
            @can('create', \App\Models\Expedient::class)
                <x-mary-button icon="o-plus" class="btn-primary shadow-xl shadow-primary/20 rounded-xl sm:rounded-2xl h-11 sm:h-14 px-4 sm:px-8 font-black uppercase text-[10px] sm:text-xs tracking-widest border-none hover:scale-105 transition-premium" link="{{ route('expedients.create') }}">
                    <span class="hidden sm:inline">Nuevo Expediente</span>
                    <span class="sm:hidden">Nuevo</span>
                </x-mary-button>
            @endcan
        </x-slot:actions>
    </x-mary-header>

    @if(count($selected) > 0)
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-10 flex flex-col md:flex-row justify-between items-center animate-in zoom-in-95 duration-500 gap-4 sm:gap-6">
            <div class="flex items-center gap-4 sm:gap-6 flex-wrap">
                <div class="w-10 h-10 sm:w-14 sm:h-14 bg-primary text-white rounded-xl flex items-center justify-center font-bold text-lg sm:text-xl shadow-sm">
                    {{ count($selected) }}
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-primary uppercase text-[10px] tracking-[0.3em]">Gestión Grupal</span>
                    <span class="text-slate-600 dark:text-slate-300 text-xs font-bold mt-0.5">Expedientes seleccionados</span>
                </div>
                <div class="h-8 w-[1px] bg-primary/20 hidden md:block"></div>
                <div class="flex items-center gap-2">
                    @can('changeLocation', \App\Models\Expedient::class)
                        <x-mary-button label="Mover" icon="o-map-pin" wire:click="showBulkMove" class="btn-sm btn-primary rounded-lg px-3 sm:px-4" />
                    @endcan
                </div>
            </div>
            <x-mary-button icon="o-x-mark" wire:click="$set('selected', [])" class="btn-ghost btn-circle btn-sm hover:bg-rose-500/10 hover:text-rose-500 transition-premium" />
        </div>
    @endif

    <x-mary-card class="premium-card p-3 sm:p-6 overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 sm:gap-6 mb-6 sm:mb-10 p-1 sm:p-2">
            <div class="sm:col-span-2">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por código, RFC o nombre..." />
            </div>
            <div>
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Estado" />
            </div>
            <div>
                <x-mary-select wire:model.live="branch_id" :options="$branches" option-label="name" option-value="id" placeholder="Sede" />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-12 sm:h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        @if(!$isAdmin && strlen(trim($search)) < 2)
            <div class="py-12 sm:py-20 text-center">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-4 text-primary">
                    <x-mary-icon name="o-magnifying-glass" class="w-8 h-8 sm:w-10 sm:h-10" />
                </div>
                <h3 class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 mb-1">Búsqueda de Expedientes</h3>
                <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto px-4">Ingresa el código, RFC o nombre del trabajador en el buscador superior para consultar su disponibilidad y solicitarlo.</p>
            </div>
        @else
        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'expedient', 'label' => 'Expediente', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 pl-4 sm:pl-6'],
                ['key' => 'employee.branch.name', 'label' => 'Sede', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden md:table-cell'],
                ['key' => 'volume_number', 'label' => 'Tomo', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden sm:table-cell'],
                ['key' => 'current_status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
                ['key' => 'currentLocation.short_label', 'label' => 'Ubicación', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden lg:table-cell'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-3 sm:pr-6']
            ]" :rows="$expedients" :sort-by="$sortBy" with-pagination selectable wire:model="selected" class="table-premium">
                
                @scope('cell_expedient', $expedient)
                    <div class="flex flex-col py-2 pl-2 sm:pl-4">
                        <span class="font-bold text-slate-800 dark:text-slate-100 leading-tight text-sm sm:text-base">
                            {{ $expedient->employee?->first_name ?? 'Sin empleado' }} {{ $expedient->employee?->last_name ?? '' }}
                        </span>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $expedient->expedient_code }}</span>
                            <span class="lg:hidden text-[9px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $expedient->currentLocation?->short_label ?? 'Sin ubicación' }}</span>
                        </div>
                    </div>
                @endscope

                @scope('cell_current_status', $expedient)
                    @php
                        $expStatusClasses = match($expedient->current_status) {
                            \App\Enums\ExpedientStatus::Available  => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                            \App\Enums\ExpedientStatus::Requested  => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                            \App\Enums\ExpedientStatus::Reserved   => 'bg-sky-500/10 text-sky-600 border-sky-500/20',
                            \App\Enums\ExpedientStatus::Loaned     => 'bg-primary/10 text-primary border-primary/20',
                            \App\Enums\ExpedientStatus::Returned   => 'bg-violet-500/10 text-violet-600 border-violet-500/20',
                            \App\Enums\ExpedientStatus::Archived   => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                            \App\Enums\ExpedientStatus::InStorage  => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                            \App\Enums\ExpedientStatus::Lost       => 'bg-rose-500/10 text-rose-600 border-rose-500/20',
                            default                                => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase border {{ $expStatusClasses }} whitespace-nowrap">
                        {{ $expedient->current_status->label() }}
                    </span>
                @endscope

                @scope('cell_actions', $expedient)
                    <div class="flex items-center gap-1 sm:gap-2 pr-2 sm:pr-4">
                        <x-mary-button link="{{ route('expedients.show', $expedient) }}" class="btn-ghost btn-xs sm:btn-sm text-slate-500 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Ver detalles">
                            <x-mary-icon name="o-eye" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                        @can('loans.create')
                            @if($expedient->isAvailable())
                                <x-mary-button link="{{ route('loans.request', ['expedient' => $expedient->id]) }}" class="btn-ghost btn-xs sm:btn-sm text-secondary hover:bg-secondary/5 rounded-xl transition-premium group/btn" tooltip="Solicitar Préstamo">
                                    <x-mary-icon name="o-document-text" class="w-4 h-4 group-hover/btn:scale-110" />
                                </x-mary-button>
                            @endif
                        @endcan
                        @can('update', $expedient)
                            <x-mary-button link="{{ route('expedients.edit', $expedient) }}" class="btn-ghost btn-xs sm:btn-sm text-slate-500 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Editar">
                                <x-mary-icon name="o-pencil" class="w-4 h-4 group-hover/btn:scale-110" />
                            </x-mary-button>
                        @endcan
                    </div>
                @endscope

            </x-mary-table>
        </div>
        @endif
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
                        @php
                            $glossaryClasses = match($status) {
                                \App\Enums\ExpedientStatus::Available  => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                \App\Enums\ExpedientStatus::Requested  => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                \App\Enums\ExpedientStatus::Reserved   => 'bg-sky-500/10 text-sky-600 border-sky-500/20',
                                \App\Enums\ExpedientStatus::Loaned     => 'bg-primary/10 text-primary border-primary/20',
                                \App\Enums\ExpedientStatus::Returned   => 'bg-violet-500/10 text-violet-600 border-violet-500/20',
                                \App\Enums\ExpedientStatus::Archived   => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                                \App\Enums\ExpedientStatus::InStorage  => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                                \App\Enums\ExpedientStatus::Lost       => 'bg-rose-500/10 text-rose-600 border-rose-500/20',
                                default                                => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                            };
                        @endphp
                        <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase border w-fit group-hover:scale-105 transition-premium {{ $glossaryClasses }}">
                            {{ $status->label() }}
                        </span>
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
