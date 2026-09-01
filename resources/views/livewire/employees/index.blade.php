<div>
    <x-mary-header title="Plantilla de Personal" subtitle="Búsqueda y consulta de expedientes de trabajadores" class="mb-6 sm:mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary shadow-xl shadow-primary/20 rounded-xl sm:rounded-2xl h-11 sm:h-14 px-4 sm:px-8 font-black uppercase text-[10px] sm:text-xs tracking-widest border-none hover:scale-105 transition-premium" wire:click="$set('createEmployeeModal', true)">
                <span class="hidden sm:inline">Nuevo Empleado</span>
                <span class="sm:hidden">Nuevo</span>
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="premium-card p-3 sm:p-6 overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10 p-1 sm:p-2">
            <div class="sm:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Escribe el nombre o RFC para buscar en el directorio..." />
            </div>
            <div>
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-12 sm:h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'name', 'label' => 'Empleado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 pl-4 sm:pl-6'],
                ['key' => 'rfc', 'label' => 'RFC', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden sm:table-cell'],
                ['key' => 'branch.name', 'label' => 'Sede', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden md:table-cell'],
                ['key' => 'employment_status', 'label' => 'Estatus', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-3 sm:pr-6']
            ]" :rows="$employees" :sort-by="$sortBy" :with-pagination="$employees instanceof \Illuminate\Contracts\Pagination\Paginator" class="table-premium">

                @scope('cell_name', $employee)
                    <div class="flex flex-col py-2 pl-2 sm:pl-4">
                        <span class="font-bold text-slate-800 dark:text-slate-100 leading-tight text-sm sm:text-base">{{ $employee->first_name }} {{ $employee->last_name }}</span>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            <span class="sm:hidden text-[10px] font-black text-primary uppercase tracking-widest">{{ $employee->rfc }}</span>
                            @if(($employee->source ?? 'local') === 'api')
                                <span class="px-2 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-500 text-[8px] sm:text-[9px] font-black uppercase border border-indigo-500/20">API Central</span>
                            @endif
                        </div>
                    </div>
                @endscope

                @scope('cell_rfc', $employee)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ $employee->rfc }}</span>
                        @if($employee->employee_number)
                            <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-0.5">No. {{ $employee->employee_number }}</span>
                        @endif
                    </div>
                @endscope

                @scope('cell_employment_status', $employee)
                    <div class="px-2.5 sm:px-4 py-1 sm:py-1.5 rounded-xl {{ $employee->employment_status === 'active' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20' }} text-[8px] sm:text-[9px] font-black uppercase text-center w-fit border shadow-sm whitespace-nowrap">
                        {{ $employee->employment_status === 'active' ? 'Activo' : 'Inactivo' }}
                    </div>
                @endscope

                @scope('cell_actions', $employee)
                    <div class="flex items-center gap-1 sm:gap-2 pr-2 sm:pr-4">
                        @if(($employee->source ?? 'local') === 'api')
                            <x-mary-button icon="o-plus-circle" wire:click="createExpedientFromApi('{{ $employee->rfc }}')" spinner="createExpedientFromApi" class="btn-ghost btn-xs sm:btn-sm text-emerald-500 hover:bg-emerald-500/5 rounded-xl transition-premium group/btn" tooltip="Registrar y Crear Expediente" />
                        @else
                            @if($employee->expedients->count() > 0)
                                <x-mary-button icon="o-folder-open" link="{{ route('expedients.show', $employee->expedients->first()) }}" class="btn-ghost btn-xs sm:btn-sm text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Ver Expediente" />
                            @else
                                <x-mary-button icon="o-plus-circle" link="{{ route('expedients.create', $employee) }}" class="btn-ghost btn-xs sm:btn-sm text-emerald-500 hover:bg-emerald-500/5 rounded-xl transition-premium group/btn" tooltip="Crear Expediente" />
                            @endif
                            <x-mary-button icon="o-eye" link="{{ route('employees.show', $employee) }}" class="btn-ghost btn-xs sm:btn-sm text-slate-500 dark:text-slate-400 hover:text-slate-600 dark:text-slate-300 rounded-xl transition-premium group/btn" tooltip="Ver Perfil" />
                        @endif
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>

    <!-- Modal de Creación Manual de Empleado -->
    <x-mary-modal wire:model="createEmployeeModal" class="p-4 sm:p-8 modal-wide">
        <div class="flex items-center justify-between gap-4 mb-6 sm:mb-8 border-b border-slate-100 pb-4 sm:pb-6">
            <div>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Registrar Nuevo Empleado</h3>
                <p class="text-[10px] sm:text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Captura manual para personal de nuevo ingreso</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center font-black">
                <x-mary-icon name="o-user-plus" class="w-5 h-5 sm:w-6 sm:h-6" />
            </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <x-mary-input label="RFC (10 caracteres) *" wire:model="rfc" placeholder="Ej: GOMA850215" icon="o-identification" class="uppercase" maxlength="10" />
                </div>
                <div>
                    <x-mary-input label="No. de Empleado (Opcional)" wire:model="employee_number" placeholder="Ej: 10452" icon="o-hashtag" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <x-mary-input label="Nombre(s) *" wire:model="first_name" placeholder="Nombre(s)..." icon="o-user" />
                </div>
                <div>
                    <x-mary-input label="Apellidos *" wire:model="last_name" placeholder="Apellidos..." icon="o-user" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full justify-end pt-6">
                <x-mary-button label="Cancelar" wire:click="$set('createEmployeeModal', false)" class="btn-ghost rounded-xl font-bold" />
                <x-mary-button label="Guardar Empleado" wire:click="saveEmployee" class="btn-primary rounded-xl px-8 font-black uppercase text-xs tracking-wider shadow-lg shadow-primary/20" spinner="saveEmployee" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>

