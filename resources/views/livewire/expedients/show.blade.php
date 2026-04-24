<div>
    <x-mary-header title="Expediente: {{ $expedient->expedient_code }}" subtitle="Detalles y movimientos físicos" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('expedients.index') }}">Volver</x-mary-button>
            <x-mary-button icon="o-document-text" class="btn-secondary" link="{{ route('loans.request', ['expedient' => $expedient->id]) }}">Solicitar</x-mary-button>
            <x-mary-button icon="o-pencil" class="btn-primary" link="{{ route('expedients.edit', $expedient) }}">Editar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Info Principal -->
        <div class="md:col-span-2 space-y-6">
            <x-mary-card title="Información del Empleado">
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-stat title="Nombre Completo" value="{{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}" icon="o-user" />
                    <x-mary-stat title="RFC" value="{{ $expedient->employee->rfc }}" icon="o-identification" />
                    <x-mary-stat title="Departamento" value="{{ $expedient->employee->department->name ?? 'N/A' }}" icon="o-building-office" />
                    <x-mary-stat title="Sucursal" value="{{ $expedient->employee->branch->name ?? 'N/A' }}" icon="o-building-storefront" />
                </div>
            </x-mary-card>

            <x-mary-card title="Detalles del Archivo">
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-stat title="Código" value="{{ $expedient->expedient_code }}" icon="o-qr-code" />
                    <x-mary-stat title="Tomo" value="{{ $expedient->volume_number }}" icon="o-book-open" />
                    <x-mary-stat title="Ubicación Física" value="{{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}" icon="o-map-pin" />
                    
                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <x-mary-icon name="o-tag" class="w-8 h-8" />
                        </div>
                        <div class="stat-title">Estado</div>
                        <div class="stat-value text-{{ $expedient->current_status->color() }} text-xl mt-2">
                            {{ $expedient->current_status->label() }}
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Sidebar Detalles -->
        <div class="space-y-6">
            @if($expedient->currentHolder)
                <x-mary-card title="En Posesión De" class="bg-primary/10 border-primary">
                    <div class="flex items-center space-x-4">
                        <x-mary-icon name="o-user-circle" class="w-12 h-12 text-primary" />
                        <div>
                            <p class="font-bold">{{ $expedient->currentHolder->name }}</p>
                            <p class="text-sm text-gray-500">{{ $expedient->currentHolder->email }}</p>
                        </div>
                    </div>
                </x-mary-card>
            @endif

            <x-mary-card title="Historial Reciente" separator>
                <div class="overflow-y-auto max-h-96 pr-2">
                    <ul class="steps steps-vertical w-full">
                        @forelse($expedient->movements->take(5) as $movement)
                            <li class="step step-primary" data-content="✓">
                                <div class="text-left py-2">
                                    <div class="font-semibold">{{ $movement->movement_type->value }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-sm mt-1">Por: {{ $movement->user->name ?? 'Sistema' }}</div>
                                    @if($movement->notes)
                                        <div class="text-xs mt-1 italic">"{{ $movement->notes }}"</div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">No hay movimientos registrados.</li>
                        @endforelse
                    </ul>
                </div>
            </x-mary-card>
        </div>
    </div>
</div>
