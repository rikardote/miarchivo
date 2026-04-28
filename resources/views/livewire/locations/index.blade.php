<div>
    <x-mary-header title="Ubicaciones Físicas" subtitle="Gestión de archivos, gavetas y cajones" class="mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary shadow-2xl shadow-primary/20 rounded-2xl h-14 px-8 font-black uppercase text-xs tracking-widest border-none hover:scale-105 transition-premium" wire:click="create">Nueva Ubicación</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="premium-card p-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 p-2">
            <div class="md:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por nombre, tipo o sucursal..." />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'location_type', 'label' => 'Tipo', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'archive_name', 'label' => 'Nombre del Archivo', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'branch.name', 'label' => 'Sucursal', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'details', 'label' => 'Detalles', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'is_active', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$locations" :sort-by="$sortBy" with-pagination class="table-premium">

                @scope('cell_location_type', $location)
                    <div class="flex items-center gap-3 pl-4">
                        <div class="w-1.5 h-6 bg-primary/20 rounded-full"></div>
                        <span class="font-black text-slate-900 dark:text-white dark:text-slate-100 tracking-tight">{{ $location->location_type }}</span>
                    </div>
                @endscope

                @scope('cell_details', $location)
                    <div class="flex flex-col py-2">
                        @if($location->cabinet) 
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 dark:text-slate-300">
                                <span class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 dark:text-slate-400 tracking-tighter">Gaveta:</span>
                                <span>{{ $location->cabinet }}</span>
                            </div>
                        @endif
                        @if($location->drawer) 
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 dark:text-slate-300 mt-1">
                                <span class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 dark:text-slate-400 tracking-tighter">Cajón:</span>
                                <span>{{ $location->drawer }}</span>
                            </div>
                        @endif
                        @if($location->alpha_range) 
                            <div class="flex items-center gap-1.5 text-[10px] font-black text-primary uppercase tracking-widest mt-1.5 bg-primary/5 px-2 py-0.5 rounded-lg border border-primary/10 w-fit">
                                <span>Rango: {{ $location->alpha_range }}</span>
                            </div>
                        @endif
                    </div>
                @endscope

                @scope('cell_is_active', $location)
                    <div class="px-4 py-1.5 rounded-xl {{ $location->is_active ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 border-rose-500/20' }} text-[9px] font-black uppercase text-center w-fit border shadow-sm">
                        {{ $location->is_active ? 'Activo' : 'Inactivo' }}
                    </div>
                @endscope

                @scope('cell_actions', $location)
                    <div class="flex items-center gap-2 pr-4">
                        <x-mary-button wire:click="edit({{ $location->id }})" class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Editar">
                            <x-mary-icon name="o-pencil" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>

    <!-- Modal Form -->
    <x-mary-modal wire:model="locationModal" class="p-6">
        <div class="space-y-8 mt-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-xl shadow-primary/30">
                    <x-mary-icon name="o-map-pin" class="w-7 h-7" />
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white dark:text-white tracking-tighter leading-none">{{ $editing ? 'Editar Ubicación' : 'Nueva Ubicación' }}</h3>
                    <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-1">Configuración de espacio físico</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <x-mary-select label="Sucursal / Sede" wire:model="branch_id" :options="$branches" placeholder="Seleccione la sede física..." icon="o-building-office" class="rounded-2xl h-14 px-5 border-slate-100" />
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-mary-select label="Tipo de Archivo" wire:model="location_type" :options="$types" placeholder="Seleccione tipo..." icon="o-tag" class="rounded-2xl h-14 px-5 border-slate-100" />
                    <x-mary-input label="Nombre del Archivo" wire:model="archive_name" placeholder="Ej: Archivo Central" icon="o-pencil" class="rounded-2xl h-14 px-5 border-slate-100" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-mary-input label="Gaveta / Mueble" wire:model="cabinet" placeholder="Ej: A-01" icon="o-archive-box" class="rounded-2xl h-14 px-5 border-slate-100" />
                    <x-mary-input label="Cajón / Nivel" wire:model="drawer" placeholder="Ej: 3" icon="o-list-bullet" class="rounded-2xl h-14 px-5 border-slate-100" />
                </div>

                <x-mary-input label="Rango Alfabético (Opcional)" wire:model="alpha_range" placeholder="Ej: A-M" icon="o-language" class="rounded-2xl h-14 px-5 border-slate-100" />
                
                <x-mary-textarea label="Notas Operativas" wire:model="notes" placeholder="Información adicional relevante..." rows="3" class="rounded-2xl border-slate-100 p-4" />

                <div class="p-6 bg-slate-50 dark:bg-white/5 rounded-[1.5rem] border border-slate-100 dark:border-white/5">
                    <x-mary-checkbox label="Esta ubicación está disponible para uso activo" wire:model="is_active" class="checkbox-primary" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full mt-6">
                <x-mary-button label="Cancelar" wire:click="$toggle('locationModal')" class="btn-ghost rounded-xl" />
                <x-mary-button label="Guardar Ubicación" wire:click="save" class="btn-primary rounded-xl" spinner="save" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
