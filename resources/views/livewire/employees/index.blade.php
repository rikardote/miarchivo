<div>
    <x-mary-header title="Directorio de Empleados" subtitle="Personal sincronizado desde el sistema principal">
    </x-mary-header>

    <x-mary-card>
        <div class="mb-6 flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por RFC, número o nombre..." />
            </div>
            <div class="pb-1">
                <x-mary-toggle label="Solo con Expediente" wire:model.live="onlyWithExpedient" class="checkbox-primary" tight />
            </div>
        </div>

        <x-mary-table :headers="[
            ['key' => 'employee_number', 'label' => 'No. Emp'],
            ['key' => 'rfc', 'label' => 'RFC'],
            ['key' => 'first_name', 'label' => 'Nombre'],
            ['key' => 'last_name', 'label' => 'Apellidos'],
            ['key' => 'department.name', 'label' => 'Departamento'],
            ['key' => 'employment_status', 'label' => 'Estado'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-1']
        ]" :rows="$employees" :sort-by="$sortBy" with-pagination>

            @scope('cell_employment_status', $employee)
                <x-mary-badge :value="ucfirst($employee->employment_status)" class="badge-{{ $employee->employment_status === 'active' ? 'success' : 'neutral' }}" />
            @endscope

            @scope('cell_actions', $employee)
                <div class="flex space-x-2">
                    @if($employee->expedients->count() > 0)
                        <x-mary-button icon="o-folder-open" link="{{ route('expedients.show', $employee->expedients->first()) }}" class="btn-sm btn-info btn-outline" tooltip="Ver Expediente" />
                    @endif
                    <x-mary-button icon="o-eye" link="{{ route('employees.show', $employee) }}" class="btn-sm btn-ghost" tooltip="Ver Perfil" />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
