<div>
    <x-mary-header title="Directorio de Personal" subtitle="Consulta a la API de empleados; registra expedientes físicos del personal" class="mb-10">
        <x-slot:actions>
            <x-mary-button label="Nuevo Empleado" icon="o-user-plus" wire:click="openCreateModal" class="btn-outline rounded-2xl h-14 px-6 font-bold uppercase text-xs tracking-widest border-slate-200 hover:bg-slate-50 transition-premium" />
            @can('create', \App\Models\Expedient::class)
                <x-mary-button label="Nuevo Expediente" icon="o-plus" link="{{ route('expedients.create') }}" class="btn-primary shadow-2xl shadow-primary/20 rounded-2xl h-14 px-8 font-black uppercase text-xs tracking-widest border-none hover:scale-105 transition-premium" />
            @endcan
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="premium-card p-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-2 items-center">
            <div class="md:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por RFC, número o nombre..." />
            </div>
            <div class="flex items-center justify-center p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5 h-14">
                <x-mary-toggle label="Solo con Expediente" wire:model.live="onlyWithExpedient" class="checkbox-primary" tight />
            </div>
        </div>

        @if($apiSearched && $employees->isEmpty())
            <div class="py-16 text-center">
                <x-mary-icon name="o-user" class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
                <p class="font-bold text-slate-500 dark:text-slate-400">Sin resultados para "{{ $search }}"</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">No se encontró en la base local ni en la API de empleados.</p>
            </div>
        @else
        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'employee_number', 'label' => 'No. Emp', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'rfc', 'label' => 'RFC', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'first_name', 'label' => 'Nombre', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'employment_status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$employees" :sort-by="$sortBy" :with-pagination="$employees instanceof \Illuminate\Contracts\Pagination\Paginator" class="table-premium">

                @scope('cell_employee_number', $employee)
                    <div class="flex items-center gap-3 pl-4">
                        <div class="w-1.5 h-6 bg-primary/20 rounded-full"></div>
                        <span class="font-black text-slate-900 dark:text-white dark:text-slate-100 tracking-tight">{{ $employee->employee_number ?? 'S/N' }}</span>
                    </div>
                @endscope

                @scope('cell_first_name', $employee)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-tight">{{ $employee->first_name }} {{ $employee->last_name }}</span>
                        <span class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-tighter">{{ $employee->rfc }}</span>
                            @if(($employee->source ?? 'local') === 'api')
                                <span class="text-[9px] font-black uppercase text-sky-600 dark:text-sky-400 bg-sky-500/10 border border-sky-500/20 rounded-md px-1.5 py-0.5">API</span>
                            @endif
                        </span>
                    </div>
                @endscope

                @scope('cell_employment_status', $employee)
                    <div class="px-4 py-1.5 rounded-xl {{ $employee->employment_status === 'active' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20' }} text-[9px] font-black uppercase text-center w-fit border shadow-sm">
                        {{ $employee->employment_status === 'active' ? 'Activo' : 'Inactivo' }}
                    </div>
                @endscope

                @scope('cell_actions', $employee)
                    <div class="flex items-center gap-2 pr-4">
                        @if(($employee->source ?? 'local') === 'api')
                            <x-mary-button icon="o-plus-circle" wire:click="createExpedientFromApi('{{ $employee->rfc }}')" spinner="createExpedientFromApi" class="btn-ghost btn-sm text-emerald-500 hover:bg-emerald-500/5 rounded-xl transition-premium group/btn" tooltip="Registrar y Crear Expediente" />
                        @else
                            @if($employee->expedients->count() > 0)
                                <x-mary-button icon="o-folder-open" link="{{ route('expedients.show', $employee->expedients->first()) }}" class="btn-ghost btn-sm text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Ver Expediente" />
                            @else
                                <x-mary-button icon="o-plus-circle" link="{{ route('expedients.create', $employee) }}" class="btn-ghost btn-sm text-emerald-500 hover:bg-emerald-500/5 rounded-xl transition-premium group/btn" tooltip="Crear Expediente" />
                            @endif
                            <x-mary-button icon="o-eye" link="{{ route('employees.show', $employee) }}" class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-slate-600 dark:text-slate-300 rounded-xl transition-premium group/btn" tooltip="Ver Perfil" />
                        @endif
                    </div>
                @endscope

            </x-mary-table>
        </div>
        @endif
    </x-mary-card>

    <!-- Modal de Creación Manual de Empleado -->
    <x-mary-modal wire:model="createEmployeeModal" class="p-8 modal-wide">
        <div class="flex items-center justify-between gap-4 mb-8 border-b border-slate-100 pb-6">
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Registrar Nuevo Empleado</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Captura manual para personal de nuevo ingreso</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center font-black">
                <x-mary-icon name="o-user-plus" class="w-6 h-6" />
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-mary-input label="RFC (10 caracteres) *" wire:model="rfc" placeholder="Ej: GOMA850215" icon="o-identification" class="uppercase" maxlength="10" />
                </div>
                <div>
                    <x-mary-input label="No. de Empleado (Opcional)" wire:model="employee_number" placeholder="Ej: 10452" icon="o-hashtag" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

