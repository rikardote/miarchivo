<div>
    <x-mary-header title="Perfil de Empleado" subtitle="{{ $employee->full_name }}" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('employees.index') }}">Volver</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Info del Empleado -->
        <div class="space-y-8">
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-4 space-y-6">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">Información General</h3>
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center text-white relative">
                                <span class="text-xl font-black tracking-tighter">
                                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-identification" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Número de Empleado</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $employee->employee_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-finger-print" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">RFC</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $employee->rfc }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-briefcase" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Puesto</p>
                                <p class="text-sm font-black text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ htmlspecialchars_decode($employee->position ?? 'No especificado') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-map-pin" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Centro de Trabajo</p>
                                <p class="text-sm font-black text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $employee->work_center ?? 'No especificado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-slate-100 mt-6 flex justify-between items-center">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Estado Laboral</p>
                        <x-mary-badge 
                            :value="strtoupper($employee->employment_status)" 
                            class="badge-{{ $employee->employment_status === 'active' ? 'success' : 'neutral' }} font-black px-4 py-3" />
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Expedientes -->
        <div class="lg:col-span-2 space-y-8">
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">Expedientes de Archivo</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">Acervo físico registrado</p>
                        </div>
                        @if($employee->expedients->isNotEmpty())
                            <x-mary-button icon="o-plus" label="Nuevo Tomo" link="{{ route('expedients.create', $employee) }}" class="btn-primary rounded-2xl h-12 shadow-lg shadow-primary/20" />
                        @endif
                    </div>

                    @if($employee->expedients->isEmpty())
                        <div class="text-center py-20 bg-slate-50/50 dark:bg-white/5 rounded-3xl border-2 border-dashed border-slate-100 dark:border-white/5">
                            <div class="p-6 bg-white dark:bg-slate-800 rounded-3xl w-fit mx-auto shadow-sm mb-6">
                                <x-mary-icon name="o-inbox" class="w-12 h-12 text-slate-300" />
                            </div>
                            <h4 class="font-black text-slate-800 dark:text-slate-100 mb-2">No hay expedientes</h4>
                            <p class="text-sm text-slate-500 mb-8 max-w-xs mx-auto">Este empleado aún no tiene carpetas registradas en el sistema de archivo físico.</p>
                            <x-mary-button class="btn-primary rounded-2xl px-8 h-12" icon="o-plus" label="Crear Primer Expediente" link="{{ route('expedients.create', $employee) }}" />
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table table-md">
                                <thead>
                                    <tr class="text-slate-400 border-b-2 border-slate-50">
                                        <th class="text-[10px] font-black uppercase tracking-widest py-4">Código</th>
                                        <th class="text-[10px] font-black uppercase tracking-widest py-4">Tomo</th>
                                        <th class="text-[10px] font-black uppercase tracking-widest py-4">Ubicación Actual</th>
                                        <th class="text-[10px] font-black uppercase tracking-widest py-4">Estatus</th>
                                        <th class="py-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employee->expedients as $expedient)
                                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                                            <td class="font-black text-slate-800 dark:text-slate-100 py-5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-1.5 h-6 bg-primary/20 rounded-full group-hover:bg-primary transition-colors"></div>
                                                    {{ $expedient->expedient_code }}
                                                </div>
                                            </td>
                                            <td class="font-bold text-slate-600 dark:text-slate-300">{{ $expedient->volume_number }}</td>
                                            <td class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-tight">
                                                {{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}
                                            </td>
                                            <td>
                                                <div class="px-3 py-1 rounded-lg bg-{{ $expedient->current_status->color() }}/10 text-{{ $expedient->current_status->color() }} text-[9px] font-black uppercase text-center w-fit border border-{{ $expedient->current_status->color() }}/20">
                                                    {{ $expedient->current_status->label() }}
                                                </div>
                                            </td>
                                            <td class="text-right pr-4">
                                                <x-mary-button icon="o-eye" link="{{ route('expedients.show', $expedient) }}" class="btn-ghost btn-circle btn-sm text-slate-400 hover:text-primary hover:bg-primary/5" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </x-mary-card>
        </div>
    </div>
</div>
