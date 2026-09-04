<div>
    @php
        $statusConfig = match($expedient->current_status) {
            \App\Enums\ExpedientStatus::Available => [
                'badge' => 'badge-success',
                'dot' => 'bg-emerald-500',
                'label' => 'Disponible en estantería',
            ],
            \App\Enums\ExpedientStatus::Loaned => [
                'badge' => 'badge-warning',
                'dot' => 'bg-amber-500',
                'label' => 'En préstamo activo',
            ],
            \App\Enums\ExpedientStatus::Requested => [
                'badge' => 'badge-info',
                'dot' => 'bg-sky-500',
                'label' => 'Solicitud pendiente',
            ],
            \App\Enums\ExpedientStatus::Lost => [
                'badge' => 'badge-error',
                'dot' => 'bg-rose-500',
                'label' => 'Reportado extraviado',
            ],
            default => [
                'badge' => 'badge-ghost',
                'dot' => 'bg-slate-400',
                'label' => 'En resguardo',
            ],
        };
    @endphp

    <!-- ENCABEZADO DE PÁGINA -->
    <x-page-header 
        title="Editar Expediente: {{ $expedient->expedient_code }}" 
        subtitle="Actualizar asignación de ubicación física y metadatos del tomo" 
        icon="o-pencil-square" 
        class="mb-8"
    >
        <x-slot:actions>
            <x-mary-button 
                icon="o-arrow-left" 
                label="Volver al Expediente" 
                class="btn-ghost rounded-xl font-bold" 
                link="{{ route('expedients.show', $expedient) }}" 
            />
        </x-slot:actions>
    </x-page-header>

    <!-- COCKPIT EN DOS COLUMNAS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- COLUMNA IZQUIERDA: Tarjeta Institucional del Expediente y Empleado -->
        <div class="lg:col-span-5 xl:col-span-4 space-y-6">
            
            <!-- Credencial de Identidad del Expediente -->
            <div class="premium-card p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-primary/10 text-primary">
                            <x-mary-icon name="o-identification" class="w-5 h-5" />
                        </div>
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Titular del Expediente</span>
                    </div>
                    <span class="badge {{ $statusConfig['badge'] }} badge-sm font-bold gap-1 py-2 px-2.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }} animate-pulse"></span>
                        {{ $statusConfig['label'] }}
                    </span>
                </div>

                @if($expedient->employee)
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#073256] to-[#0a416e] text-white flex items-center justify-center font-black text-lg shadow-md shadow-[#073256]/20 shrink-0">
                                {{ mb_substr($expedient->employee->first_name ?? 'E', 0, 1) }}{{ mb_substr($expedient->employee->last_name ?? 'X', 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-base font-black text-slate-900 dark:text-white truncate" title="{{ $expedient->employee->full_name }}">
                                    {{ $expedient->employee->full_name }}
                                </h2>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 truncate">
                                    {{ $expedient->employee->position ?? 'Personal Institucional' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#041d33] border border-slate-100 dark:border-[#0c4472]/50">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">RFC</span>
                                <span class="font-mono font-black text-slate-800 dark:text-slate-200">{{ $expedient->employee->rfc }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#041d33] border border-slate-100 dark:border-[#0c4472]/50">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">No. Empleado</span>
                                <span class="font-mono font-black text-slate-800 dark:text-slate-200">{{ $expedient->employee->employee_number ?? 'S/N' }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-xs font-bold text-slate-400 italic">
                        Sin empleado vinculado directamente.
                    </div>
                @endif

                <!-- Ubicación Actual -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-2">Ubicación Física Actual</span>
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 dark:bg-[#041d33] border border-slate-200/80 dark:border-[#0c4472] flex items-start gap-3">
                        <div class="p-2 rounded-xl bg-primary/10 text-primary shrink-0 mt-0.5">
                            <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                        </div>
                        <div class="text-xs min-w-0">
                            @if($expedient->currentLocation)
                                <div class="font-black text-slate-900 dark:text-white">
                                    Gaveta / Archivero {{ $expedient->currentLocation->cabinet }} — Cajón {{ $expedient->currentLocation->drawer }}
                                </div>
                                @if($expedient->currentLocation->alpha_range)
                                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-0.5">
                                        Rango alfabético: <span class="font-mono font-black text-primary dark:text-[#C4A462]">{{ $expedient->currentLocation->alpha_range }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="font-bold text-amber-600 dark:text-amber-400">
                                    Sin ubicación física asignada actualmente
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Resumen de Tomo y Fechas -->
                <div class="grid grid-cols-2 gap-2 text-xs pt-2">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#041d33] border border-slate-100 dark:border-[#0c4472]/50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Volumen Físico</span>
                        <span class="font-black text-primary dark:text-[#C4A462]">Tomo {{ $expedient->volume_number ?? 1 }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#041d33] border border-slate-100 dark:border-[#0c4472]/50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Apertura</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $expedient->opened_at ? $expedient->opened_at->format('d/m/Y') : 'No reg.' }}</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- COLUMNA DERECHA: Formulario de Reubicación y Metadatos -->
        <div class="lg:col-span-7 xl:col-span-8">
            <div class="premium-card p-6 sm:p-8">
                
                <x-mary-form wire:submit="save">
                    
                    <!-- SECCIÓN 1: Reubicación Física -->
                    <div class="space-y-5">
                        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="p-2 rounded-xl bg-[#073256] text-[#C4A462]">
                                <x-mary-icon name="o-archive-box" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Reubicación Física en Archivero</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Selecciona el archivero y cajón de destino para actualizar su trazabilidad.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-mary-select 
                                    label="1. Archivero / Gaveta" 
                                    wire:model.live="selectedCabinet" 
                                    :options="$cabinets" 
                                    placeholder="Seleccionar archivero..."
                                    icon="o-building-office-2" 
                                />
                            </div>

                            <div>
                                <x-mary-select 
                                    label="2. Cajón y Rango Alfabético" 
                                    wire:model.live="location_id" 
                                    :options="$drawers" 
                                    placeholder="{{ empty($selectedCabinet) ? 'Primero selecciona un archivero...' : 'Selecciona un cajón...' }}"
                                    :disabled="empty($selectedCabinet)"
                                    icon="o-inbox-stack" 
                                />
                            </div>
                        </div>

                        <!-- Comparador Visual de Cambio de Ubicación -->
                        @if($location_id && $location_id !== $expedient->current_location_id)
                            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 dark:bg-emerald-950/30 flex flex-col sm:flex-row items-center justify-between gap-3 animate-in fade-in duration-200">
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-200">
                                    <span class="p-1.5 rounded-lg bg-white dark:bg-[#073256] shadow-sm text-slate-500">
                                        {{ $expedient->currentLocation ? "Gav. {$expedient->currentLocation->cabinet} › Cajón {$expedient->currentLocation->drawer}" : 'Sin Ubicación' }}
                                    </span>
                                    <x-mary-icon name="o-arrow-right" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                    <span class="p-1.5 rounded-lg bg-emerald-600 text-white shadow-sm font-black">
                                        {{ $newLocation ? "Gav. {$newLocation->cabinet} › Cajón {$newLocation->drawer}" : 'Nueva Ubicación' }}
                                    </span>
                                </div>
                                <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300">
                                    Se registrará el traslado en la bitácora
                                </span>
                            </div>
                        @endif

                        <div>
                            <x-mary-textarea 
                                label="Motivo o Justificación del Movimiento (Opcional)" 
                                wire:model="movement_notes" 
                                placeholder="Ej. Reubicado por reorganización de archivo, saturación de espacio, o cambio de módulo..."
                                rows="2" 
                                hint="Quedará registrado permanentemente en el historial de movimientos."
                            />
                        </div>
                    </div>

                    <!-- SECCIÓN 2: Metadatos Físicos del Expediente -->
                    <div class="space-y-5 pt-4">
                        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="p-2 rounded-xl bg-[#073256] text-[#C4A462]">
                                <x-mary-icon name="o-document-duplicate" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Metadatos Físicos del Expediente</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Configura el volumen o tomo y la fecha de creación física.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-mary-input 
                                    label="Número de Tomo / Volumen" 
                                    type="number" 
                                    min="1" 
                                    max="99" 
                                    wire:model="volume_number" 
                                    icon="o-book-open"
                                    hint="Identificador del tomo físico (ej. 1 para Tomo I, 2 para Tomo II)." 
                                />
                            </div>

                            <div>
                                <x-mary-input 
                                    label="Fecha de Apertura Física" 
                                    type="date" 
                                    wire:model="opened_at" 
                                    icon="o-calendar"
                                    hint="Fecha en que se originó el resguardo físico del expediente." 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <x-slot:actions>
                        <div class="flex items-center justify-between w-full pt-4 border-t border-slate-100 dark:border-slate-800">
                            <x-mary-button 
                                label="Cancelar y Volver" 
                                icon="o-x-mark" 
                                class="btn-ghost rounded-xl font-bold text-xs uppercase" 
                                link="{{ route('expedients.show', $expedient) }}" 
                            />
                            
                            <x-mary-button 
                                label="Guardar Cambios" 
                                icon="o-check" 
                                class="btn-primary rounded-xl px-8 font-black text-xs uppercase tracking-wider shadow-lg shadow-[#073256]/20" 
                                type="submit" 
                                spinner="save" 
                            />
                        </div>
                    </x-slot:actions>

                </x-mary-form>

            </div>
        </div>

    </div>
</div>

