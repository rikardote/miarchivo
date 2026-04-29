<div>
    <x-mary-header title="Directorio de Empleados" subtitle="Personal sincronizado desde el sistema principal" class="mb-10">
    </x-mary-header>

    <x-mary-card class="premium-card p-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 p-2">
            <div class="md:col-span-3">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por RFC, número o nombre..." />
            </div>
            <div class="flex items-center justify-center p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5 h-14">
                <x-mary-toggle label="Solo con Expediente" wire:model.live="onlyWithExpedient" class="checkbox-primary" tight />
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'employee_number', 'label' => 'No. Emp', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'rfc', 'label' => 'RFC', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'first_name', 'label' => 'Nombre', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'employment_status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$employees" :sort-by="$sortBy" with-pagination class="table-premium">

                @scope('cell_employee_number', $employee)
                    <div class="flex items-center gap-3 pl-4">
                        <div class="w-1.5 h-6 bg-primary/20 rounded-full"></div>
                        <span class="font-black text-slate-900 dark:text-white dark:text-slate-100 tracking-tight">{{ $employee->employee_number }}</span>
                    </div>
                @endscope

                @scope('cell_first_name', $employee)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-tight">{{ $employee->first_name }} {{ $employee->last_name }}</span>
                        <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-tighter mt-1">{{ $employee->rfc }}</span>
                    </div>
                @endscope

                @scope('cell_employment_status', $employee)
                    <div class="px-4 py-1.5 rounded-xl {{ $employee->employment_status === 'active' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20' }} text-[9px] font-black uppercase text-center w-fit border shadow-sm">
                        {{ $employee->employment_status === 'active' ? 'Activo' : 'Inactivo' }}
                    </div>
                @endscope

                @scope('cell_actions', $employee)
                    <div class="flex items-center gap-2 pr-4">
                        @if($employee->expedients->count() > 0)
                            <x-mary-button icon="o-folder-open" link="{{ route('expedients.show', $employee->expedients->first()) }}" class="btn-ghost btn-sm text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Ver Expediente" />
                        @else
                            <x-mary-button icon="o-plus-circle" link="{{ route('expedients.create', $employee) }}" class="btn-ghost btn-sm text-emerald-500 hover:bg-emerald-500/5 rounded-xl transition-premium group/btn" tooltip="Crear Expediente" />
                        @endif
                        <x-mary-button icon="o-eye" link="{{ route('employees.show', $employee) }}" class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-slate-600 dark:text-slate-300 rounded-xl transition-premium group/btn" tooltip="Ver Perfil" />
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>
</div>
