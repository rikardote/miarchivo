<div>
    <x-mary-header title="Alta Continua de Expedientes" subtitle="Registro uno a uno: crea el expediente, imprime su etiqueta y guárdalo en la gaveta" separator />

    <div class="max-w-5xl">
        <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- 1. Configuración de sesión -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">1. Elige el cajón de trabajo</h3>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Paso 1</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-mary-select
                            label="Gaveta / Archivero"
                            wire:model.live="selectedCabinet"
                            :options="$cabinets"
                            placeholder="Selecciona archivero..."
                            icon="o-building-office"
                        />
                    </div>
                    <div>
                        <x-mary-select
                            label="Cajón / Rango"
                            wire:model.live="location_id"
                            :options="$drawers"
                            placeholder="{{ empty($selectedCabinet) ? 'Primero selecciona un archivero...' : 'Selecciona un cajón...' }}"
                            :disabled="empty($selectedCabinet)"
                            icon="o-inbox-stack"
                        />
                    </div>
                </div>

                @if (empty($selectedCabinet))
                    <p class="text-xs font-bold text-slate-400 mt-3">Elige la gaveta para ver sus cajones. La cola mostrará solo empleados sin expediente cuyo apellido corresponde al rango del cajón.</p>
                @endif

                @if ($selectedLocation)
                    <div class="relative py-6">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-slate-100"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-white dark:bg-slate-900 px-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Sesión activa — Cajón {{ $selectedLocation->drawer }} · {{ $selectedLocation->cabinet }}</span>
                        </div>
                    </div>

                    <!-- Resumen del cajón -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl">
                            <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Rango alfabético</div>
                            <div class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white">{{ $selectedLocation->alpha_range ?? 'Sin rango' }}</div>
                        </div>
                        <div class="p-4 bg-amber-50 dark:bg-amber-500/10 rounded-2xl">
                            <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-amber-600/70 mb-1">Pendientes en cajón</div>
                            <div class="text-xl sm:text-2xl font-black text-amber-600">{{ $pendingInRange }}</div>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl">
                            <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-emerald-600/70 mb-1">Ya con expediente</div>
                            <div class="text-xl sm:text-2xl font-black text-emerald-600">{{ $createdInRange }}</div>
                        </div>
                    </div>

                    @if (! $readyToPrint)
                        @if ($currentEmployee)
                            <!-- Empleado actual en el visor -->
                            <div class="rounded-3xl border-2 border-dashed border-primary/30 bg-gradient-to-br from-primary/5 via-transparent to-transparent p-6 sm:p-8">
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary mb-2">Carpeta física en mano · Verifica antes de crear</p>
                                        <h4 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-tight">{{ $currentEmployee->full_name }}</h4>
                                    </div>
                                    @if ($currentEmployee->employment_status !== 'active')
                                        <span class="text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 px-3 py-1.5 rounded-xl border border-rose-500/20 shrink-0">Baja</span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">RFC</p>
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $currentEmployee->rfc }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">No. Empleado</p>
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $currentEmployee->employee_number ?? '—' }}</p>
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Puesto</p>
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $currentEmployee->position ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-4">
                                    <x-mary-button
                                        label="Crear Expediente y Etiqueta"
                                        icon="o-document-plus"
                                        wire:click="createAndPrint"
                                        spinner="createAndPrint"
                                        class="btn-primary flex-1 rounded-2xl h-14 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/20 border-none"
                                    />
                                    <x-mary-button
                                        label="Aplazar (carpeta no está en este lote)"
                                        icon="o-arrow-uturn-left"
                                        wire:click="openSkipModal"
                                        spinner="openSkipModal"
                                        class="btn-ghost rounded-2xl h-14 font-black uppercase text-xs tracking-widest text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10"
                                    />
                                </div>

                                @if ($nextEmployee)
                                    <div class="mt-6 pt-4 border-t border-dashed border-slate-200 dark:border-white/10 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <x-mary-icon name="o-arrow-down" class="w-4 h-4 text-slate-300 dark:text-slate-600 shrink-0" />
                                            <div class="min-w-0">
                                                <p class="text-[9px] font-black uppercase tracking-[0.25em] text-slate-300 dark:text-slate-600 mb-0.5">Siguiente en la cola</p>
                                                <p class="text-sm font-black text-slate-500 dark:text-slate-400 uppercase truncate">{{ $nextEmployee->full_name }}</p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 shrink-0 hidden sm:inline">{{ $nextEmployee->rfc }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Cajón atendido -->
                            <div class="rounded-3xl bg-emerald-50 dark:bg-emerald-500/10 p-8 text-center">
                                <x-mary-icon name="o-check-circle" class="w-12 h-12 text-emerald-500 mx-auto mb-4" />
                                <h4 class="text-xl font-black text-emerald-700 dark:text-emerald-300 uppercase tracking-tight">¡Cajón al día!</h4>
                                <p class="text-sm font-bold text-emerald-600/70 mt-2">
                                    @if ($skippedEmployees->isNotEmpty())
                                        Todos los pendientes visibles están aplazados abajo. Reincorpóralos o cambia de cajón para continuar.
                                    @else
                                        No quedan empleados pendientes en este cajón. Cambia de gaveta/cajón para continuar.
                                    @endif
                                </p>
                            </div>
                        @endif
                    @else
                        <!-- Expediente creado: imprimir etiqueta -->
                        <div class="rounded-3xl bg-emerald-50/60 dark:bg-emerald-500/5 border border-emerald-200/60 dark:border-emerald-500/20 p-6 sm:p-8" x-data="{ printLabel() { const frame = this.$refs.labelFrame; if (frame) { frame.contentWindow.print(); } } }">
                            <div class="flex items-center justify-between gap-4 mb-6">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600 mb-2">Expediente creado</p>
                                    <h4 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ \App\Models\Expedient::find($lastCreatedExpedientId)?->expedient_code }}</h4>
                                    <p class="text-xs font-bold text-slate-500 mt-1">Pega la etiqueta en la carpeta y guárdala en el cajón {{ $selectedLocation->cabinet }} · Cajón {{ $selectedLocation->drawer }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col items-center gap-4">
                                <iframe
                                    x-ref="labelFrame"
                                    wire:key="label-{{ $lastCreatedExpedientId }}"
                                    src="{{ route('expedients.print', ['expedient' => $lastCreatedExpedientId]) }}"
                                    class="w-[330px] h-[210px] bg-white rounded-xl border border-slate-200 shadow-sm"
                                    title="Etiqueta del expediente"
                                ></iframe>

                                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                                    <x-mary-button
                                        label="Imprimir Etiqueta"
                                        icon="o-printer"
                                        class="btn-primary rounded-2xl h-14 px-10 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/20 border-none"
                                        x-on:click="printLabel()"
                                    />
                                    <x-mary-button
                                        label="Etiqueta pegada — Siguiente"
                                        icon="o-arrow-right"
                                        wire:click="confirmNext"
                                        spinner="confirmNext"
                                        class="btn-outline btn-primary rounded-2xl h-14 px-10 font-black uppercase text-xs tracking-widest"
                                    />
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Aplazamientos del Censo (Trazabilidad P1) -->
                    @if ($deferredSkips->isNotEmpty() && ! $readyToPrint)
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-white/5">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-wider text-amber-600">Aplazamientos Registrados en este Cajón ({{ $deferredSkips->count() }})</p>
                                    <p class="text-[10px] text-slate-400 font-bold">Carpetas físicas no localizadas que no detienen el censo</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach ($deferredSkips as $skip)
                                    <div wire:key="skip-{{ $skip->id }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-amber-50/50 dark:bg-amber-500/5 border border-amber-200/40 rounded-2xl p-4">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase">{{ $skip->employee->last_name }} {{ $skip->employee->first_name }}</p>
                                                <span class="badge badge-warning badge-sm text-[9px] font-black uppercase">Aplazado</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[10px] font-bold text-slate-400">
                                                <span>RFC: {{ $skip->employee->rfc }}</span>
                                                <span>•</span>
                                                <span class="text-amber-700 dark:text-amber-400">Motivo: {{ $skip->reason }}</span>
                                                <span>•</span>
                                                <span>Por: {{ $skip->user->name ?? 'Sistema' }} ({{ $skip->created_at->format('d/m/Y H:i') }})</span>
                                            </div>
                                        </div>
                                        <x-mary-button
                                            label="Censar Ahora"
                                            icon="o-arrow-uturn-right"
                                            wire:click="restoreSkipped({{ $skip->employee_id }})"
                                            spinner="restoreSkipped"
                                            class="btn-warning btn-sm rounded-xl font-black uppercase text-[10px] tracking-wider shrink-0 shadow-sm"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-sm font-bold text-slate-400 text-center mt-10">
                        Selecciona la gaveta y el cajón para comenzar. La cola mostrará solo empleados sin expediente cuyo apellido corresponde al rango del cajón.
                    </p>
                @endif
            </div>
        </x-mary-card>
    </div>

    <!-- Modal de Confirmación de Aplazamiento -->
    <x-mary-modal wire:model="showSkipModal" class="p-6">
        <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center font-black">
                <x-mary-icon name="o-clock" class="w-6 h-6" />
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Aplazar Registro de Censo</h3>
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-wider">Trazabilidad de incidencia física</p>
            </div>
        </div>

        @if($currentEmployee)
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-4">
                El empleado <strong class="text-slate-900 dark:text-white uppercase">{{ $currentEmployee->full_name }}</strong> será omitido de la cola inmediata y quedará registrado como pendiente en este cajón.
            </p>
        @endif

        <div class="space-y-4">
            <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Motivo del aplazamiento:</label>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-50">
                    <input type="radio" wire:model.live="skipReason" value="Carpeta física no localizada en lote" class="radio radio-primary radio-sm" />
                    <span class="text-xs font-bold text-slate-700">Carpeta física no localizada en lote</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-50">
                    <input type="radio" wire:model.live="skipReason" value="Expediente en préstamo o trámite externo" class="radio radio-primary radio-sm" />
                    <span class="text-xs font-bold text-slate-700">Expediente en préstamo o trámite externo</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-50">
                    <input type="radio" wire:model.live="skipReason" value="Inconsistencia en documentos o RFC" class="radio radio-primary radio-sm" />
                    <span class="text-xs font-bold text-slate-700">Inconsistencia en documentos o RFC</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-50">
                    <input type="radio" wire:model.live="skipReason" value="Otro motivo" class="radio radio-primary radio-sm" />
                    <span class="text-xs font-bold text-slate-700">Otro motivo</span>
                </label>
            </div>

            @if($skipReason === 'Otro motivo')
                <x-mary-input label="Especifique el motivo" placeholder="Detalles de la anomalía..." wire:model="customSkipReason" class="input-sm mt-2" />
            @endif
        </div>

        <x-slot:actions>
            <div class="flex gap-3 justify-end pt-4 w-full">
                <x-mary-button label="Cancelar" wire:click="$toggle('showSkipModal')" class="btn-ghost rounded-xl px-4" />
                <x-mary-button label="Confirmar Aplazamiento" wire:click="confirmSkip" class="btn-warning rounded-xl px-6 font-bold uppercase text-xs tracking-wider" spinner="confirmSkip" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
