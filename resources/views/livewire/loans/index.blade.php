<div>
    <x-mary-header title="Préstamos" subtitle="Listado de solicitudes de expedientes">
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" link="{{ route('loans.request') }}">Nueva Solicitud</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-3">
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Todos los estados" />
            </div>
            <div>
                <x-mary-button wire:click="$set('status', '')" icon="o-x-mark" class="btn-ghost w-full">Limpiar</x-mary-button>
            </div>
        </div>

        <x-mary-table :headers="[
            ['key' => 'expedient.expedient_code', 'label' => 'Expediente'],
            ['key' => 'requester.name', 'label' => 'Solicitante'],
            ['key' => 'status', 'label' => 'Estado'],
            ['key' => 'requested_at', 'label' => 'Fecha Solicitud'],
            ['key' => 'due_date', 'label' => 'Vencimiento'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-1']
        ]" :rows="$loans" :sort-by="$sortBy" with-pagination>

            @scope('cell_status', $loan)
                <x-mary-badge :value="$loan->status->label()" class="badge-{{ $loan->status->color() }}" />
            @endscope

            @scope('cell_requested_at', $loan)
                {{ $loan->requested_at->format('d/m/Y H:i') }}
            @endscope

            @scope('cell_due_date', $loan)
                @if($loan->due_date)
                    <span class="{{ $loan->isOverdue() ? 'text-error font-bold' : '' }}">
                        {{ $loan->due_date->format('d/m/Y') }}
                    </span>
                @else
                    -
                @endif
            @endscope

            @scope('cell_actions', $loan)
                <div class="flex space-x-2">
                    <x-mary-button icon="o-eye" link="{{ route('loans.manage', $loan) }}" class="btn-sm btn-ghost" tooltip="Gestionar" />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
