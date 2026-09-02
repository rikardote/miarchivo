<div>
    <x-mary-header title="{{ $expedient->expedient_code }}" subtitle="Detalles y movimientos físicos" separator class="mb-4 sm:mb-6">
        <x-slot:actions>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <x-mary-button icon="o-arrow-left" class="btn-ghost btn-sm sm:btn-md" link="{{ route('expedients.index') }}">Volver</x-mary-button>
                @can('loans.create')
                    @if($expedient->isAvailable())
                        <x-mary-button icon="o-document-text" class="btn-secondary btn-sm sm:btn-md" link="{{ route('loans.request', ['expedient' => $expedient->id]) }}">Solicitar</x-mary-button>
                    @else
                        <x-mary-button icon="o-document-text" class="btn-secondary btn-sm sm:btn-md" disabled label="No Disponible" />
                    @endif
                @endcan
                @can('update', $expedient)
                    @if($expedient->current_status->value !== 'lost')
                        <x-mary-button icon="o-exclamation-triangle" class="btn-error btn-outline btn-sm sm:btn-md" label="Extraviado" wire:click="markAsLost" />
                    @else
                        <x-mary-button icon="o-check-circle" class="btn-success btn-outline btn-sm sm:btn-md" label="Recuperado" wire:click="markAsFound" />
                    @endif
                    <x-mary-button icon="o-pencil" class="btn-primary btn-sm sm:btn-md" link="{{ route('expedients.edit', $expedient) }}">Editar</x-mary-button>
                @endcan
            </div>
        </x-slot:actions>
    </x-mary-header>

    <!-- PANEL DE CONTROL OPERATIVO / LOCALIZACIÓN RÁPIDA (Sección 15) -->
    <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 bg-white dark:bg-slate-900 rounded-3xl mb-8 overflow-hidden">
        <div class="p-2 sm:p-4">
            <!-- Barra Superior: Identificador y Estatus Operativo -->
            <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Ficha de Consulta Rápida</span>
                        <span class="badge badge-neutral badge-sm font-mono text-[10px] uppercase">Tomo {{ $expedient->volume_number }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">{{ $expedient->expedient_code }}</h1>
                </div>

                @php
                    $statusConfig = match($expedient->current_status) {
                        \App\Enums\ExpedientStatus::Available => [
                            'badge' => 'badge-success',
                            'dot' => 'bg-emerald-500 animate-pulse',
                            'desc' => 'En estantería física • Disponible para préstamo'
                        ],
                        \App\Enums\ExpedientStatus::Loaned => [
                            'badge' => 'badge-warning',
                            'dot' => 'bg-amber-500 animate-pulse',
                            'desc' => 'Fuera de sala • En préstamo activo'
                        ],
                        \App\Enums\ExpedientStatus::Requested => [
                            'badge' => 'badge-info',
                            'dot' => 'bg-sky-500',
                            'desc' => 'Solicitud pendiente de entrega'
                        ],
                        \App\Enums\ExpedientStatus::Lost => [
                            'badge' => 'badge-error',
                            'dot' => 'bg-rose-500 animate-ping',
                            'desc' => 'Alerta: Reportado como extraviado'
                        ],
                        default => [
                            'badge' => 'badge-ghost',
                            'dot' => 'bg-slate-400',
                            'desc' => 'En resguardo institucional'
                        ],
                    };
                @endphp
                <div class="text-left sm:text-right">
                    <div class="badge {{ $statusConfig['badge'] }} gap-2 py-3 px-4 font-black text-xs uppercase tracking-wider shadow-sm">
                        <span class="w-2 h-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                        {{ $expedient->current_status->label() }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1 font-bold">{{ $statusConfig['desc'] }}</p>
                </div>
            </div>

            <!-- 4 Respuestas Operativas Inmediatas -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 pt-6">
                <!-- 1. ¿De quién es? -->
                <div class="bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100/70 dark:hover:bg-slate-800 transition-colors rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3">
                        <x-mary-icon name="o-user" class="w-4 h-4" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">¿De quién es?</p>
                    <p class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100 uppercase truncate">{{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}</p>
                    <div class="mt-3 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                            <span class="text-[11px] text-slate-400 font-medium">RFC:</span>
                            <span class="font-mono font-black text-primary">{{ $expedient->employee->rfc }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                            <span class="text-[11px] text-slate-400 font-medium">No. Empleado:</span>
                            <span class="font-bold">{{ $expedient->employee->employee_number ?? 'S/N' }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
                            {{ $expedient->employee->position ?? 'Personal Operativo' }}
                        </p>
                    </div>
                </div>

                <!-- 2. ¿Dónde está físicamente? -->
                <div class="bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100/70 dark:hover:bg-slate-800 transition-colors rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-3">
                        <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">¿Dónde está?</p>
                    @if($expedient->currentLocation)
                        <p class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100 leading-tight truncate">
                            {{ $expedient->currentLocation->archive_name }}
                        </p>
                        <div class="mt-3 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-[11px] text-slate-400 font-medium">Ubicación:</span>
                                <span class="text-emerald-700 dark:text-emerald-400 font-black">G-{{ $expedient->currentLocation->cabinet }} • Cajón {{ $expedient->currentLocation->drawer }}</span>
                            </div>
                            @if($expedient->currentLocation->alpha_range)
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                    <span class="text-[11px] text-slate-400 font-medium">Rango:</span>
                                    <span class="badge badge-ghost badge-sm text-[9px] font-mono">{{ $expedient->currentLocation->alpha_range }}</span>
                                </div>
                            @endif
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
                                {{ $expedient->currentLocation->branch->name ?? 'Delegación Estatal' }}
                            </p>
                        </div>
                    @else
                        <p class="text-sm sm:text-base font-black text-amber-600 dark:text-amber-400">Sin ubicación</p>
                        <p class="text-xs text-slate-400 mt-2">No se ha asignado a ningún archivero físico.</p>
                    @endif
                </div>

                <!-- 3. ¿Quién lo tiene y cuándo regresa? -->
                <div class="bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100/70 dark:hover:bg-slate-800 transition-colors rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center mb-3">
                        <x-mary-icon name="o-shield-check" class="w-4 h-4" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Custodia / Préstamo</p>
                    @php
                        $activeLoan = $expedient->activeLoan();
                    @endphp
                    @if($expedient->currentHolder)
                        <p class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100 truncate">{{ $expedient->currentHolder->name }}</p>
                        <div class="mt-3 space-y-1.5 text-xs">
                            @if($activeLoan && $activeLoan->delivered_at)
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                    <span class="text-[11px] text-slate-400 font-medium">Salió:</span>
                                    <span class="font-bold">{{ $activeLoan->delivered_at->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($activeLoan && $activeLoan->due_date)
                                @php
                                    $isOverdue = $activeLoan->due_date->isPast();
                                    $daysDiff = abs((int) now()->diffInDays($activeLoan->due_date, false));
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] text-slate-400 font-medium">Vence:</span>
                                    <span class="font-bold {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-200' }}">{{ $activeLoan->due_date->format('d/m/Y') }}</span>
                                </div>
                                <p class="text-[11px] font-black {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
                                    {{ $isOverdue ? "¡Vencido hace {$daysDiff} día(s)!" : "Restan {$daysDiff} día(s)" }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm sm:text-base font-black text-emerald-700 dark:text-emerald-400">En resguardo</p>
                        <div class="mt-3 space-y-1 text-xs text-slate-500 dark:text-slate-400">
                            <p class="text-[11px]">Bajo resguardo del Archivo Central.</p>
                            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">Sin préstamo activo</p>
                        </div>
                    @endif
                </div>

                <!-- 4. Último Movimiento y Auditoría -->
                <div class="bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100/70 dark:hover:bg-slate-800 transition-colors rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center mb-3">
                        <x-mary-icon name="o-clock" class="w-4 h-4" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Última Trazabilidad</p>
                    @php
                        $lastMovement = $expedient->movements->first();
                        $lastAudit = $expedient->currentLocation?->latestAudit;
                    @endphp
                    @if($lastMovement)
                        <p class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100 leading-tight">
                            {{ $lastMovement->movement_type->label() }}
                        </p>
                        <div class="mt-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Fecha:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $lastMovement->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-[11px] truncate pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
                                Por: <strong class="text-slate-800 dark:text-slate-100">{{ $lastMovement->user->name ?? 'Sistema' }}</strong>
                            </p>
                        </div>
                    @else
                        <p class="text-sm font-bold text-slate-400">Sin movimientos registrados</p>
                    @endif

                    @if($lastAudit)
                        <div class="mt-2.5 pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60 text-[10px] text-slate-400 flex items-center justify-between">
                            <span>Auditoría de cajón:</span>
                            <span class="text-slate-700 dark:text-slate-200 font-bold">{{ $lastAudit->created_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
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
