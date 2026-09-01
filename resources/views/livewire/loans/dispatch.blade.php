<div>
    <x-mary-header title="Despacho y Extracción" subtitle="Centro operativo de surtido y recepción de expedientes en planta baja" class="mb-6 sm:mb-10">
        <x-slot:actions>
            <x-mary-button label="Hoja de Surtido" icon="o-printer" link="{{ route('loans.picking-list') }}" external class="btn-primary shadow-xl shadow-primary/20 rounded-xl sm:rounded-2xl h-11 sm:h-14 px-4 sm:px-6 font-black uppercase text-[10px] sm:text-xs tracking-widest" />
        </x-slot:actions>
    </x-mary-header>

    <!-- Barra Superior de Escaneo Rápido -->
    <div class="premium-card p-4 sm:p-6 mb-6 bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-3xl shadow-xl">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="p-3 bg-primary/20 text-primary-content rounded-2xl">
                    <x-mary-icon name="o-qr-code" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black tracking-tight text-white">Escaneo Operativo Rápido</h3>
                    <p class="text-[10px] sm:text-xs text-slate-400">Escanea con lector láser o cámara para despachar o devolver al instante.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto flex-1 max-w-md">
                <form wire:submit.prevent="processScan" class="w-full">
                    <div class="relative">
                        <input 
                            id="dispatch-scanner-input"
                            type="text" 
                            wire:model="scannedCode" 
                            placeholder="Apunta y escanea aquí..." 
                            autocomplete="off"
                            class="w-full bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 rounded-2xl h-12 pl-4 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                        />
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-xs btn-primary rounded-xl">
                            <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                        </button>
                    </div>
                </form>

                <button onclick="startDispatchScanner()" class="btn btn-ghost btn-circle bg-slate-800 text-white hover:bg-slate-700 border border-slate-700" tooltip="Abrir Cámara">
                    <x-mary-icon name="o-camera" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Visor de Cámara para Móvil -->
        <div id="dispatch-reader-container" class="hidden mt-4 overflow-hidden rounded-2xl border-2 border-primary/40 bg-slate-950 p-2" wire:ignore>
            <div id="dispatch-reader" class="rounded-xl overflow-hidden min-h-[250px]"></div>
            <button onclick="stopDispatchScanner()" class="btn btn-ghost btn-sm text-rose-400 w-full mt-2">Cerrar Cámara</button>
        </div>
    </div>

    <!-- Pestañas de Modo Operativo -->
    <div class="flex items-center gap-2 sm:gap-4 mb-6 border-b border-slate-200 dark:border-white/10 pb-4">
        <button 
            wire:click="$set('tab', 'to_extract')" 
            class="flex items-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl font-black text-xs sm:text-sm tracking-wide transition-premium {{ $tab === 'to_extract' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
        >
            <x-mary-icon name="o-arrow-up-tray" class="w-4 h-4" />
            <span>Por Extraer (Surtido)</span>
            @if($totalPending > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'to_extract' ? 'bg-white text-primary' : 'bg-amber-500 text-white' }}">{{ $totalPending }}</span>
            @endif
        </button>

        <button 
            wire:click="$set('tab', 'to_return')" 
            class="flex items-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl font-black text-xs sm:text-sm tracking-wide transition-premium {{ $tab === 'to_return' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
        >
            <x-mary-icon name="o-arrow-down-tray" class="w-4 h-4" />
            <span>Por Reubicar (Devueltos)</span>
            @if($totalReturns > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'to_return' ? 'bg-white text-primary' : 'bg-blue-500 text-white' }}">{{ $totalReturns }}</span>
            @endif
        </button>
    </div>

    <!-- Filtros de la Lista -->
    <div class="premium-card p-3 sm:p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="{{ $tab === 'to_extract' ? 'sm:col-span-2' : 'sm:col-span-3' }}">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Filtrar por código, empleado o solicitante..." />
            </div>
            @if($tab === 'to_extract')
                <div>
                    <x-mary-select wire:model.live="selectedLocationId" :options="$locations" option-label="full_label" option-value="id" placeholder="Filtrar por Archivero / Gaveta..." />
                </div>
            @endif
        </div>
    </div>

    @if(count($selectedLoans) > 0)
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 mb-6 flex justify-between items-center animate-in zoom-in-95 duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary text-white rounded-xl flex items-center justify-center font-bold text-sm">
                    {{ count($selectedLoans) }}
                </div>
                <span class="text-xs font-black uppercase text-primary tracking-widest">Expedientes seleccionados</span>
            </div>
            @if($tab === 'to_extract')
                <x-mary-button label="Marcar Surtidos y Enviar a RH" icon="o-check" wire:click="extractBulk" class="btn-primary btn-sm rounded-xl" />
            @else
                <x-mary-button label="Guardar Seleccionados en Gaveta" icon="o-archive-box-arrow-down" wire:click="rearchiveBulk" class="btn-success btn-sm rounded-xl text-white" />
            @endif
        </div>
    @endif

    <!-- Contenido de Surtido o Reubicación -->
    @if($items->isEmpty())
        <div class="premium-card p-12 text-center">
            <div class="w-16 h-16 bg-emerald-500/10 rounded-3xl flex items-center justify-center mx-auto mb-4 text-emerald-500">
                <x-mary-icon name="o-check-badge" class="w-10 h-10" />
            </div>
            <h3 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-1">
                {{ $tab === 'to_extract' ? '¡Todo el archivo está al día!' : '¡No hay expedientes pendientes por archivar!' }}
            </h3>
            <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto">
                {{ $tab === 'to_extract' ? 'No hay solicitudes pendientes por surtir en este momento.' : 'Todos los expedientes devueltos ya se encuentran guardados en sus gavetas correspondientes.' }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($items as $loan)
                <div class="premium-card p-4 sm:p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-primary/40 transition-premium">
                    <div class="flex items-start gap-3 sm:gap-4 flex-1">
                        @php
                            $canSelect = ($tab === 'to_return') || ($tab === 'to_extract' && $loan->status === \App\Enums\LoanStatus::Approved);
                        @endphp

                        @if($canSelect)
                            <input type="checkbox" wire:model.live="selectedLoans" value="{{ $loan->id }}" class="checkbox checkbox-primary rounded-lg mt-1" />
                        @else
                            <div class="tooltip mt-1" data-tip="Requiere aprobación previa de RH">
                                <input type="checkbox" disabled class="checkbox checkbox-disabled opacity-20 cursor-not-allowed rounded-lg" />
                            </div>
                        @endif

                        <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $tab === 'to_extract' ? ($loan->status === \App\Enums\LoanStatus::Approved ? 'bg-amber-500/10 text-amber-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-400') : 'bg-emerald-500/10 text-emerald-600' }} rounded-2xl flex items-center justify-center shrink-0">
                            <x-mary-icon name="{{ $tab === 'to_extract' ? ($loan->status === \App\Enums\LoanStatus::Approved ? 'o-arrow-up-tray' : 'o-clock') : 'o-archive-box-arrow-down' }}" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>

                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ $loan->expedient?->expedient_code ?? 'ELIMINADO' }}</span>
                                @if($tab === 'to_extract')
                                    @if($loan->status === \App\Enums\LoanStatus::Approved)
                                        <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                            Aprobado (Listo para Extraer)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                            En Espera de Aprobación (RH)
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                        {{ $loan->expedient?->current_status?->label() ?? 'Devuelto' }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">
                                {{ $loan->expedient?->employee?->first_name ?? 'Sin empleado' }} {{ $loan->expedient?->employee?->last_name ?? '' }}
                                <span class="text-slate-400 font-normal">({{ $loan->expedient?->employee?->rfc ?? 'N/A' }})</span>
                            </p>

                            <div class="flex items-center gap-2 text-[11px] text-slate-500 flex-wrap pt-1">
                                <span class="flex items-center gap-1 font-bold">
                                    <x-mary-icon name="o-user" class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ $tab === 'to_extract' ? 'Solicitó: ' . ($loan->requester?->name ?? 'Desconocido') : 'Devuelto por: ' . ($loan->requester?->name ?? 'Desconocido') }}</span>
                                </span>
                                <span>•</span>
                                <span class="text-slate-400">{{ $tab === 'to_extract' ? 'Solicitud: ' . ($loan->requested_at?->format('d/m/Y H:i') ?? 'N/A') : 'Devolución: ' . ($loan->returned_at?->format('d/m/Y H:i') ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación Física Destacada -->
                    <div class="w-full md:w-auto flex flex-col sm:flex-row md:flex-col lg:flex-row items-stretch sm:items-center gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-white/5">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-white/5 text-center sm:text-left min-w-[200px]">
                            <div class="text-[9px] font-black uppercase tracking-widest text-primary flex items-center gap-1">
                                <x-mary-icon name="o-map-pin" class="w-3 h-3" />
                                <span>{{ $tab === 'to_extract' ? 'Ubicación en Gaveta' : 'Guardar en Gaveta' }}</span>
                            </div>
                            <div class="text-xs sm:text-sm font-black text-slate-800 dark:text-slate-100 mt-0.5">
                                {{ $loan->expedient?->currentLocation?->full_label ?? 'Sin ubicación' }}
                            </div>
                        </div>

                        @if($tab === 'to_extract')
                            @if($loan->status === \App\Enums\LoanStatus::Approved)
                                <x-mary-button label="Marcar Surtido" icon="o-check" wire:click="extractSingle({{ $loan->id }})" class="btn-primary rounded-xl h-11 px-5 font-black uppercase text-xs tracking-wider shadow-lg shadow-primary/20" spinner="extractSingle({{ $loan->id }})" />
                            @else
                                <div class="tooltip" data-tip="Requiere aprobación previa del encargado de RH">
                                    <button class="btn btn-disabled bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-white/5 rounded-xl h-11 px-4 font-black uppercase text-[10px] tracking-wider flex items-center gap-2 cursor-not-allowed">
                                        <x-mary-icon name="o-lock-closed" class="w-4 h-4 text-slate-400" />
                                        <span>En Espera de RH</span>
                                    </button>
                                </div>
                            @endif
                        @else
                            <x-mary-button label="Guardar en Gaveta" icon="o-archive-box-arrow-down" wire:click="rearchiveSingle({{ $loan->id }})" class="btn-success rounded-xl h-11 px-5 font-black uppercase text-xs tracking-wider text-white shadow-lg shadow-emerald-500/20" spinner="rearchiveSingle({{ $loan->id }})" />
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="pt-4">
                {{ $items->links() }}
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        let dispatchHtml5QrCode = null;

        function startDispatchScanner() {
            document.getElementById('dispatch-reader-container').classList.remove('hidden');
            dispatchHtml5QrCode = new Html5Qrcode("dispatch-reader");
            const config = { fps: 15, qrbox: { width: 250, height: 250 } };

            const onSuccess = (decodedText) => {
                @this.dispatch('code-scanned', { code: decodedText });
                stopDispatchScanner();
            };

            dispatchHtml5QrCode.start({ facingMode: "environment" }, config, onSuccess)
                .catch(err => alert("Error de cámara: " + err));
        }

        function stopDispatchScanner() {
            if (dispatchHtml5QrCode) {
                dispatchHtml5QrCode.stop().then(() => {
                    document.getElementById('dispatch-reader-container').classList.add('hidden');
                }).catch(() => {
                    document.getElementById('dispatch-reader-container').classList.add('hidden');
                });
            } else {
                document.getElementById('dispatch-reader-container').classList.add('hidden');
            }
        }
    </script>
    @endpush
</div>
