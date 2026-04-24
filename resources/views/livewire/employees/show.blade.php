<div>
    <x-mary-header title="Perfil de Empleado" subtitle="{{ $employee->full_name }}" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('employees.index') }}">Volver</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="space-y-6">
            <x-mary-card title="Información General">
                <div class="flex items-center justify-center mb-6">
                    <x-mary-avatar image="https://ui-avatars.com/api/?name={{ urlencode($employee->full_name) }}&background=random" class="!w-24" />
                </div>
                
                <div class="space-y-3">
                    <x-mary-stat title="Número de Empleado" value="{{ $employee->employee_number }}" icon="o-identification" />
                    <x-mary-stat title="RFC" value="{{ $employee->rfc }}" icon="o-finger-print" />
                    <x-mary-stat title="Puesto" value="{{ $employee->position ?? 'No especificado' }}" icon="o-briefcase" />
                    <x-mary-stat title="Departamento" value="{{ $employee->department->name ?? 'Sin departamento' }}" icon="o-building-office" />
                    <x-mary-stat title="Centro de Trabajo" value="{{ $employee->work_center ?? 'No especificado' }}" icon="o-map-pin" />
                    
                    <div class="pt-4 border-t">
                        <p class="text-sm text-gray-500 mb-1">Estado Laboral</p>
                        <x-mary-badge :value="ucfirst($employee->employment_status)" class="badge-{{ $employee->employment_status === 'active' ? 'success' : 'neutral' }}" />
                    </div>
                </div>
            </x-mary-card>
        </div>

        <div class="md:col-span-2 space-y-6">
            <x-mary-card title="Expedientes de Archivo" icon="o-folder-open">
                @if($employee->expedients->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <x-mary-icon name="o-inbox" class="w-12 h-12 mb-2 opacity-50" />
                        <p>No hay expedientes registrados para este empleado.</p>
                        <x-mary-button class="btn-primary mt-4" icon="o-plus" link="{{ route('expedients.create') }}">Crear Expediente</x-mary-button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Tomo</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->expedients as $expedient)
                                    <tr>
                                        <td class="font-bold">{{ $expedient->expedient_code }}</td>
                                        <td>{{ $expedient->volume_number }}</td>
                                        <td>{{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}</td>
                                        <td>
                                            <x-mary-badge :value="$expedient->current_status->label()" class="badge-{{ $expedient->current_status->color() }}" />
                                        </td>
                                        <td>
                                            <x-mary-button icon="o-eye" link="{{ route('expedients.show', $expedient) }}" class="btn-sm btn-ghost" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-mary-card>
        </div>
        
    </div>
</div>
