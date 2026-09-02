<div>
    @php
        $statusConfig = match($expedient->current_status) {
            \App\Enums\ExpedientStatus::Available => [
                'badge' => 'badge-success',
                'dot' => 'bg-emerald-500 animate-pulse',
                'label' => 'Disponible en estantería',
            ],
            \App\Enums\ExpedientStatus::Loaned => [
                'badge' => 'badge-warning',
                'dot' => 'bg-amber-500 animate-pulse',
                'label' => 'En préstamo activo',
            ],
            \App\Enums\ExpedientStatus::Requested => [
                'badge' => 'badge-info',
                'dot' => 'bg-sky-500',
                'label' => 'Solicitud pendiente',
            ],
            \App\Enums\ExpedientStatus::Lost => [
                'badge' => 'badge-error',
                'dot' => 'bg-rose-500 animate-ping',
                'label' => 'Reportado extraviado',
            ],
            default => [
                'badge' => 'badge-ghost',
                'dot' => 'bg-slate-400',
                'label' => 'En resguardo',
            ],
        };
        $activeLoan = $expedient->activeLoan();
    @endphp

    <!-- BARRA DE ACCIONES SUPERIOR -->
    <div class="flex items-center justify-between gap-4 mb-6">
        <x-mary-button icon="o-arrow-left" class="btn-ghost btn-sm" link="{{ route('expedients.index') }}">Volver</x-mary-button>
        <div class="flex items-center gap-2 flex-wrap justify-end">
            @can('loans.create')
                @if($expedient->isAvailable())
                    <x-mary-button icon="o-document-text" class="btn-secondary btn-sm" link="{{ route('loans.request', ['expedient' => $expedient->id]) }}">Solicitar Préstamo</x-mary-button>
                @else
                    <x-mary-button icon="o-document-text" class="btn-secondary btn-sm" disabled label="No Disponible" />
                @endif
            @endcan
            @can('update', $expedient)
                @if($expedient->current_status->value !== 'lost')
                    <x-mary-button icon="o-exclamation-triangle" class="btn-error btn-outline btn-sm" label="Extraviado" wire:click="markAsLost" />
                @else
                    <x-mary-button icon="o-check-circle" class="btn-success btn-outline btn-sm" label="Recuperado" wire:click="markAsFound" />
                @endif
                <x-mary-button icon="o-pencil" class="btn-primary btn-sm" link="{{ route('expedients.edit', $expedient) }}">Editar</x-mary-button>
            @endcan
        </div>
    </div>

    <!-- TARJETA HERO UNIFICADA (Credencial Institucional del Expediente) -->
    <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 bg-white dark:bg-slate-900 rounded-3xl mb-8 overflow-hidden">
        <div class="p-4 sm:p-6 space-y-6">
            <!-- Fila Principal: Identidad del Empleado, Código y Estatus -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                    <!-- Avatar con iniciales -->
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-primary text-white flex items-center justify-center font-black text-xl sm:text-2xl shadow-lg shadow-primary/20 shrink-0">
                        {{ strtoupper(substr($expedient->employee->first_name, 0, 1) . substr($expedient->employee->last_name, 0, 1)) }}
                    </div>
                    
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono font-black text-xs sm:text-sm px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $expedient->expedient_code }}
                            </span>
                            <span class="text-xs font-black uppercase px-2.5 py-0.5 rounded-lg bg-primary/10 text-primary border border-primary/20">
                                Tomo {{ $expedient->volume_number }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-500">
                                RFC: <strong class="text-slate-800 dark:text-slate-200">{{ $expedient->employee->rfc }}</strong>
                            </span>
                            @if($expedient->employee->employee_number)
                                <span class="text-xs font-bold text-slate-400">
                                    • No. {{ $expedient->employee->employee_number }}
                                </span>
                            @endif
                        </div>
                        
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                            {{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}
                        </h1>

                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-2">
                            <span>{{ $expedient->employee->position ?? 'Personal Operativo' }}</span>
                            <span>•</span>
                            <span>{{ $expedient->employee->work_center ?? 'Delegación' }} ({{ $expedient->employee->city ?? 'B.C.' }})</span>
                            <span>•</span>
                            <span class="text-primary font-bold">{{ $expedient->employee->branch->name ?? 'Sucursal Principal' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Estatus Operativo Destacado -->
                <div class="flex lg:flex-col items-center lg:items-end justify-between gap-2 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800">
                    <div class="badge {{ $statusConfig['badge'] }} gap-2 py-3 px-4 font-black text-xs uppercase tracking-wider shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                        {{ $expedient->current_status->label() }}
                    </div>
                    <span class="text-[11px] font-bold text-slate-400">{{ $statusConfig['label'] }}</span>
                </div>
            </div>

            <!-- Fila Inferior Integrada: Ubicación Física + Custodia Actual + Última Trazabilidad -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- Ubicación Física -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                        <x-mary-icon name="o-map-pin" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ubicación Física</p>
                        @if($expedient->currentLocation)
                            <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate">
                                {{ $expedient->currentLocation->archive_name }}
                            </p>
                            <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">
                                G-{{ $expedient->currentLocation->cabinet }} • Cajón {{ $expedient->currentLocation->drawer }}
                                @if($expedient->currentLocation->alpha_range)
                                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 ml-1 text-slate-600 dark:text-slate-300">
                                        {{ $expedient->currentLocation->alpha_range }}
                                    </span>
                                @endif
                            </p>
                        @else
                            <p class="text-xs font-bold text-amber-600 mt-0.5">Sin ubicación asignada</p>
                        @endif
                    </div>
                </div>

                <!-- Custodia Actual -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                        <x-mary-icon name="o-shield-check" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Custodia / Préstamo</p>
                        @if($expedient->currentHolder)
                            <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate">
                                {{ $expedient->currentHolder->name }}
                            </p>
                            @if($activeLoan && $activeLoan->due_date)
                                @php
                                    $isOverdue = $activeLoan->due_date->isPast();
                                    $daysDiff = abs((int) now()->diffInDays($activeLoan->due_date, false));
                                @endphp
                                <p class="text-xs font-black {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500' }} mt-0.5">
                                    {{ $isOverdue ? "Vencido hace {$daysDiff} día(s)" : "Vence: {$activeLoan->due_date->format('d/m/Y')}" }}
                                </p>
                            @endif
                        @else
                            <p class="text-sm font-black text-emerald-700 dark:text-emerald-400">En resguardo</p>
                            <p class="text-xs text-slate-400 mt-0.5">Custodia del Archivo Central</p>
                        @endif
                    </div>
                </div>

                <!-- Última Trazabilidad -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0 mt-0.5">
                        <x-mary-icon name="o-clock" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Última Trazabilidad</p>
                        @php
                            $lastMovement = $expedient->movements->first();
                        @endphp
                        @if($lastMovement)
                            <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate">
                                {{ $lastMovement->movement_type->label() }}
                            </p>
                            <p class="text-xs font-bold text-slate-400 mt-0.5">
                                {{ $lastMovement->created_at->diffForHumans() }} ({{ $lastMovement->user->name ?? 'Sistema' }})
                            </p>
                        @else
                            <p class="text-xs font-bold text-slate-400 mt-0.5">Sin movimientos registrados</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </x-mary-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        
        <!-- Columna Principal: Trazabilidad y Préstamos (2 columnas) -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
            
            <!-- Historial de Movimientos Físicos -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-2 sm:p-4">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tight">Trazabilidad de Movimientos</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Historial físico cronológico del expediente</p>
                        </div>
                        <span class="badge badge-neutral badge-sm font-mono text-[10px]">{{ $expedient->movements->count() }} eventos</span>
                    </div>

                    <div class="space-y-4 max-h-[420px] overflow-y-auto pr-2">
                        @forelse($expedient->movements as $movement)
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-white/5">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <x-mary-icon name="o-arrow-path" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xs font-black uppercase text-slate-800 dark:text-slate-100 tracking-wider">
                                            {{ $movement->movement_type->label() }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400">
                                            {{ $movement->created_at->format('d/m/Y H:i') }} ({{ $movement->created_at->diffForHumans() }})
                                        </span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">
                                        Registrado por: <strong class="text-slate-900 dark:text-white">{{ $movement->user->name ?? 'Sistema' }}</strong>
                                    </p>
                                    @if($movement->notes)
                                        <p class="mt-2 text-xs italic text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 p-2.5 rounded-xl border border-slate-100 dark:border-white/5">
                                            "{{ $movement->notes }}"
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-slate-400">
                                <x-mary-icon name="o-archive-box" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                                <p class="text-xs font-bold">No hay movimientos registrados para este expediente.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-mary-card>

            <!-- Historial de Préstamos y Solicitudes -->
            @if($expedient->loanRequests->isNotEmpty())
                <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                    <div class="p-2 sm:p-4">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tight">Historial de Préstamos</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Registro de solicitudes institucionales</p>
                            </div>
                            <span class="badge badge-neutral badge-sm font-mono text-[10px]">{{ $expedient->loanRequests->count() }} solicitudes</span>
                        </div>

                        <div class="space-y-3">
                            @foreach($expedient->loanRequests as $loan)
                                <div class="p-4 rounded-2xl border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black uppercase text-slate-800 dark:text-slate-100">
                                                {{ $loan->requester->name ?? 'Usuario no disponible' }}
                                            </span>
                                            <span class="badge badge-sm font-bold uppercase text-[9px] {{ $loan->status->value === 'delivered' ? 'badge-warning' : ($loan->status->value === 'returned' ? 'badge-success' : 'badge-ghost') }}">
                                                {{ $loan->status->label() }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-3 text-[10px] font-bold text-slate-400 mt-1">
                                            <span>Solicitado: {{ $loan->requested_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                                            @if($loan->delivered_at)
                                                <span>• Entregado: {{ $loan->delivered_at->format('d/m/Y') }}</span>
                                            @endif
                                            @if($loan->returned_at)
                                                <span>• Devuelto: {{ $loan->returned_at->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                        @if($loan->observations)
                                            <p class="text-xs text-slate-500 mt-1 italic">"{{ $loan->observations }}"</p>
                                        @endif
                                    </div>
                                    @can('loans.view')
                                        <x-mary-button label="Ver Préstamo" icon="o-arrow-top-right-on-square" link="{{ route('loans.show', $loan) }}" class="btn-ghost btn-xs font-bold uppercase shrink-0" />
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-mary-card>
            @endif
        </div>

        <!-- Sidebar Lateral: Identificación Física y Metadatos (1 columna) -->
        <div class="space-y-6 sm:space-y-8">
            <!-- Identificación Física (QR y Etiqueta Térmica) -->
            @can('update', $expedient)
                <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 text-center overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">Identificación Física</h3>
                            <span class="badge badge-primary badge-sm text-[9px] font-black uppercase">Code128 / QR</span>
                        </div>
                        
                        <div class="my-4 bg-white p-4 rounded-3xl inline-block mx-auto border-4 border-slate-50 shadow-inner">
                            {!! QrCode::size(150)->generate($expedient->qr_content) !!}
                        </div>
                        
                        <p class="font-mono text-xs font-black tracking-[0.2em] text-slate-500 mb-6">{{ $expedient->expedient_code }}</p>
                        
                        <x-mary-button 
                            label="Imprimir Etiqueta Térmica" 
                            icon="o-printer" 
                            link="{{ route('expedients.print', $expedient) }}" 
                            external 
                            class="btn-primary w-full rounded-2xl h-12 font-black uppercase text-xs tracking-wider shadow-lg shadow-primary/20" 
                        />
                    </div>
                </x-mary-card>
            @endcan

            <!-- Metadatos y Control Institucional -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                <div class="p-4 space-y-4">
                    <h3 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">Ficha Técnica</h3>
                    
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-white/5">
                            <span class="text-slate-400 font-medium">Fecha de Apertura:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $expedient->opened_at?->format('d/m/Y') ?? $expedient->created_at->format('d/m/Y') }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-white/5">
                            <span class="text-slate-400 font-medium">Volumen / Tomo:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">Tomo {{ $expedient->volume_number }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-white/5">
                            <span class="text-slate-400 font-medium">Estatus del Registro:</span>
                            <span class="badge badge-success badge-sm font-bold uppercase text-[9px]">{{ $expedient->is_active ? 'Activo' : 'Archivado' }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-white/5">
                            <span class="text-slate-400 font-medium">Última Sincronización:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $expedient->employee->last_synced_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>
    
    <!-- Modal para Reporte de Extravío -->
    <x-mary-modal wire:model="showLostModal" class="p-8 modal-wide">
        <div class="flex items-center gap-6 mb-8 border-b border-slate-100 pb-6">
            <div class="w-16 h-16 bg-rose-600 text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-xl shadow-rose-500/20">
                <x-mary-icon name="o-exclamation-triangle" class="w-8 h-8" />
            </div>
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter leading-none mb-1">Reportar Extravío</h3>
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Protocolo de incidencia física</p>
            </div>
        </div>

        <div class="space-y-6">
            <x-mary-alert icon="o-exclamation-triangle" class="alert-error bg-error/10 text-error border-none rounded-2xl text-xs font-bold leading-relaxed">
                Esta acción marcará el expediente como NO DISPONIBLE para préstamos hasta que sea localizado.
            </x-mary-alert>

            <x-mary-textarea label="Notas del Incidente (Opcional)" wire:model="notes" placeholder="Describa los detalles del último avistamiento o búsqueda..." rows="4" class="rounded-2xl border-slate-100 p-4" />
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full justify-end pt-4">
                <x-mary-button label="Cancelar" wire:click="$toggle('showLostModal')" class="btn-ghost rounded-xl px-6" />
                <x-mary-button label="Confirmar Extravío" wire:click="confirmMarkAsLost" class="btn-error text-white rounded-xl px-8 font-black uppercase text-xs tracking-widest shadow-xl shadow-error/20" spinner="confirmMarkAsLost" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
