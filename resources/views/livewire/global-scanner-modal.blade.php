<div>
    <x-mary-modal wire:model="isOpen" class="modal-wide backdrop-blur-md" box-class="max-w-xl w-full" persistent>
        
        {{-- Modal Header con Indicador de Rol --}}
        <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-100 dark:border-white/10 mb-4 sm:mb-5">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-[#0F1E36]/10 dark:bg-[#C4A462]/15 text-[#0F1E36] dark:text-[#C4A462] flex items-center justify-center shrink-0">
                    <x-mary-icon name="o-qr-code" class="w-5 h-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">
                            Acción Rápida
                        </h3>
                        @if($this->isOperator)
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-sky-500/10 text-sky-600 dark:text-sky-400 uppercase tracking-wider flex items-center gap-1">
                                <x-mary-icon name="o-archive-box" class="w-2.5 h-2.5" />
                                <span>Operador de Archivo</span>
                            </span>
                        @else
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-[#0F1E36]/10 dark:bg-[#C4A462]/20 text-[#0F1E36] dark:text-[#C4A462] uppercase tracking-wider flex items-center gap-1">
                                <x-mary-icon name="o-user-group" class="w-2.5 h-2.5" />
                                <span>Encargado / Mostrador</span>
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                        @if($this->isOperator)
                            Guardado físico en gavetas, reubicación y archivo
                        @else
                            Atención a solicitantes, recepción y entrega de préstamos
                        @endif
                    </p>
                </div>
            </div>
            <button type="button" wire:click="closeScanner" class="btn btn-ghost btn-circle btn-sm text-slate-400 hover:text-rose-500">
                <x-mary-icon name="o-x-mark" class="w-4 h-4 sm:w-5 sm:h-5" />
            </button>
        </div>

        {{-- Mobile Camera & Barcode Gun Alpine Controller --}}
        <div x-data="{
            cameraActive: false,
            html5QrCode: null,
            cameraError: null,
            async toggleCamera() {
                if (this.cameraActive) {
                    this.stopCamera();
                } else {
                    this.startCamera();
                }
            },
            async startCamera() {
                this.cameraError = null;
                this.cameraActive = true;
                await this.$nextTick();
                
                try {
                    if (this.html5QrCode) {
                        await this.html5QrCode.stop().catch(() => {});
                    }
                    if (typeof Html5Qrcode === 'undefined') {
                        throw new Error('Librería de cámara no disponible.');
                    }

                    const formats = (typeof Html5QrcodeSupportedFormats !== 'undefined')
                        ? [
                            Html5QrcodeSupportedFormats.QR_CODE,
                            Html5QrcodeSupportedFormats.CODE_128
                        ]
                        : undefined;

                    this.html5QrCode = new Html5Qrcode('global-camera-viewport', formats ? { formatsToSupport: formats, verbose: false } : undefined);
                    
                    // Foco amplio panorámico ideal para capturar códigos de barras lineales largos (Code 128) y códigos QR
                    const config = {
                        fps: 20,
                        qrbox: (viewfinderWidth, viewfinderHeight) => {
                            const w = Math.floor(viewfinderWidth * 0.92);
                            const h = Math.floor(Math.min(viewfinderHeight * 0.68, 200));
                            return { width: Math.max(w, 260), height: Math.max(h, 130) };
                        },
                        aspectRatio: 1.333333,
                        videoConstraints: {
                            facingMode: { ideal: 'environment' }
                        }
                    };
                    
                    await this.html5QrCode.start(
                        { facingMode: 'environment' },
                        config,
                        (decodedText) => {
                            if (navigator.vibrate) navigator.vibrate(60);
                            this.stopCamera();
                            $wire.searchScannedCode(decodedText);
                        },
                        () => {}
                    );
                } catch (err) {
                    console.error('Camera error:', err);
                    this.cameraError = 'No se pudo acceder a la cámara. Asegúrate de otorgar permisos de cámara en tu navegador.';
                    this.cameraActive = false;
                }
            },
            async stopCamera() {
                if (this.html5QrCode) {
                    try {
                        await this.html5QrCode.stop();
                    } catch(e) {}
                    this.html5QrCode = null;
                }
                this.cameraActive = false;
            }
        }"
        x-init="$watch('$wire.isOpen', (value) => { if (!value) stopCamera(); })"
        class="mb-4 sm:mb-5">

            {{-- Botón de Cámara Móvil --}}
            <div class="mb-3">
                <button 
                    type="button" 
                    @click="toggleCamera()" 
                    class="btn btn-sm w-full rounded-xl gap-2 font-black text-xs uppercase tracking-wider transition-all cursor-pointer h-11"
                    :class="cameraActive ? 'btn-error text-white shadow-md' : 'btn-outline border-primary/40 text-primary hover:bg-primary/10'">
                    <x-mary-icon name="o-camera" class="w-4 h-4" />
                    <span x-text="cameraActive ? 'Apagar Cámara' : '📷 Usar Cámara del Celular (Barras / QR)'"></span>
                </button>
            </div>

            {{-- Viewfinder Panorámico de Cámara para Celular --}}
            <div x-show="cameraActive" x-cloak class="mb-3 rounded-2xl overflow-hidden bg-black relative border-2 border-primary shadow-xl animate-in zoom-in-95 duration-200 w-full">
                <div wire:ignore id="global-camera-viewport" class="w-full min-h-[230px] max-h-[290px]"></div>
                
                {{-- Línea láser guía para códigos de barras --}}
                <div class="pointer-events-none absolute inset-x-4 top-1/2 -translate-y-1/2 h-[2px] bg-red-500/80 shadow-[0_0_8px_rgba(239,68,68,0.9)] animate-pulse"></div>

                <div class="absolute bottom-2 inset-x-0 text-center pointer-events-none">
                    <span class="px-3 py-1 bg-black/80 text-white text-[9px] font-black uppercase tracking-wider rounded-full backdrop-blur-md border border-white/20">
                        🎯 Foco Amplio: Alinea con la barra o código QR
                    </span>
                </div>
            </div>

            <div x-show="cameraError" x-cloak class="mb-3 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 text-xs font-bold" x-text="cameraError"></div>

            {{-- Search Input (Autofocused for gun or typing) --}}
            <form wire:submit.prevent="searchExpedient" class="relative flex items-center">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <x-mary-icon name="o-magnifying-glass" class="w-4 h-4" />
                </div>
                <input 
                    type="text" 
                    wire:model="scannedCode" 
                    id="global-quick-scan-input"
                    placeholder="Código (EXP-...) o RFC..." 
                    class="block w-full pl-10 pr-20 py-3 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-xs sm:text-sm font-bold"
                    autofocus
                    autocomplete="off"
                />
                <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                    @if($scannedCode)
                        <button type="button" wire:click="$set('scannedCode', '')" class="btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-slate-600">
                            <x-mary-icon name="o-x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm rounded-lg sm:rounded-xl px-3 text-[11px] sm:text-xs font-black uppercase tracking-wider shadow-sm h-8" spinner="searchExpedient">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        {{-- Success / Error feedback --}}
        @if($errorMessage)
            <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold flex items-center gap-2.5 animate-in fade-in duration-300">
                <x-mary-icon name="o-exclamation-circle" class="w-4 h-4 shrink-0" />
                <span class="leading-tight">{{ $errorMessage }}</span>
            </div>
        @endif

        @if($successMessage)
            <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center gap-2.5 animate-in fade-in duration-300">
                <x-mary-icon name="o-check-circle" class="w-4 h-4 shrink-0" />
                <span class="leading-tight">{{ $successMessage }}</span>
            </div>
        @endif

        {{-- Expedient Details & Contextual Actions --}}
        @if($expedient)
            <div class="space-y-4 animate-in zoom-in-95 duration-300">
                
                {{-- Ficha Móvil del Expediente --}}
                <div class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-white/5 space-y-3">
                    
                    {{-- Badges de Estado y Código --}}
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-xs font-black text-primary tracking-wider bg-primary/10 px-2.5 py-1 rounded-lg">
                            {{ $expedient->expedient_code }}
                        </span>

                        @php
                            $statusClasses = match($expedient->current_status) {
                                \App\Enums\ExpedientStatus::Available => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                \App\Enums\ExpedientStatus::Loaned => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                \App\Enums\ExpedientStatus::Requested => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                                \App\Enums\ExpedientStatus::Reserved => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                                \App\Enums\ExpedientStatus::Returned => 'bg-violet-500/10 text-violet-600 border-violet-500/20',
                                default => 'bg-slate-500/10 text-slate-500 border-slate-500/20'
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $statusClasses }}">
                            {{ $expedient->current_status->label() }}
                        </span>
                    </div>

                    {{-- Nombre del Empleado Titular --}}
                    <div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight leading-snug">
                            {{ $expedient->employee?->full_name ?? 'Sin Asignar' }}
                        </h4>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-semibold flex-wrap">
                            <span>RFC: <strong>{{ $expedient->employee?->rfc ?? 'N/A' }}</strong></span>
                            @if($expedient->volume_number)
                                <span>•</span>
                                <span>Tomo {{ $expedient->volume_number }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Destacado de Ubicación Física (Especial para Operador) --}}
                    @if($this->isOperator)
                        <div class="p-3 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-900/50 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                    <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                                </div>
                                <div class="text-xs">
                                    <span class="text-[9px] uppercase font-black tracking-wider text-sky-600 dark:text-sky-400 block leading-tight">Ubicación Física Asignada</span>
                                    <strong class="text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm">{{ $expedient->currentLocation?->full_label ?? 'Sin Ubicación' }}</strong>
                                </div>
                            </div>
                            <button type="button" wire:click="$toggle('showRelocateForm')" class="btn btn-ghost btn-xs text-sky-600 dark:text-sky-400 font-bold">
                                <x-mary-icon name="o-arrows-right-left" class="w-3.5 h-3.5" />
                                <span>{{ $showRelocateForm ? 'Ocultar' : 'Reubicar' }}</span>
                            </button>
                        </div>
                    @else
                        {{-- Ubicación discreta para Encargado --}}
                        <div class="pt-2.5 border-t border-slate-200/60 dark:border-white/5 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-medium">
                                <x-mary-icon name="o-map-pin" class="w-3.5 h-3.5 text-primary shrink-0" />
                                <span class="text-[11px] sm:text-xs">Ubicación: <strong>{{ $expedient->currentLocation?->short_label ?? 'Sin Ubicación' }}</strong></span>
                            </div>
                            <button type="button" wire:click="$toggle('showRelocateForm')" class="text-[11px] font-bold text-primary hover:underline flex items-center gap-1">
                                <x-mary-icon name="o-arrows-right-left" class="w-3 h-3" />
                                <span>{{ $showRelocateForm ? 'Ocultar' : 'Mover' }}</span>
                            </button>
                        </div>
                    @endif

                    {{-- Formulario para reubicar rápidamente --}}
                    @if($showRelocateForm)
                        <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 space-y-2.5 animate-in fade-in duration-200">
                            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                Mover a otra estantería / gaveta:
                            </label>
                            <div class="flex items-center gap-2">
                                <select wire:model="targetLocationId" class="select select-sm w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-bold">
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->full_label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="quickRelocate" class="btn btn-sm btn-primary rounded-lg px-3 text-xs font-black shrink-0" spinner="quickRelocate">
                                    Mover
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ACCIONES ESPECÍFICAS SEGÚN EL ROL --}}

                {{-- CASO 1: Expediente en Préstamo Activo (Loaned) --}}
                @if($expedient->current_status === \App\Enums\ExpedientStatus::Loaned || $activeLoan)
                    <div class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-amber-500/5 border border-amber-500/20 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                <x-mary-icon name="o-clock" class="w-4 h-4" />
                            </div>
                            <div class="text-xs">
                                <p class="font-black text-slate-900 dark:text-white leading-tight">Expediente en Préstamo Activo</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Prestado a: <strong class="text-slate-800 dark:text-slate-200">{{ $activeLoan?->requester?->name ?? 'Usuario' }}</strong>
                                    @if($activeLoan?->delivered_at)
                                        ({{ $activeLoan->delivered_at->diffForHumans() }})
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Vista para el ENCARGADO: Recepción en Mostrador --}}
                        @if($this->isEncargado)
                            @can('loans.return')
                                <div class="space-y-2">
                                    <button 
                                        type="button" 
                                        wire:click="receiveReturn" 
                                        class="btn btn-primary w-full h-12 rounded-xl font-black uppercase text-xs tracking-wider shadow-md flex items-center justify-center gap-2" 
                                        spinner="receiveReturn">
                                        <x-mary-icon name="o-check-badge" class="w-4 h-4" />
                                        <span>Registrar Devolución (Recepción en Mostrador)</span>
                                    </button>
                                    <p class="text-[10px] text-center text-slate-500 dark:text-slate-400">
                                        Libera formalmente al solicitante. El expediente pasará a <strong>Devuelto (en mostrador)</strong>.
                                    </p>
                                </div>

                                {{-- Opción secundaria de guardado directo --}}
                                <div class="pt-2 border-t border-amber-500/15">
                                    <button 
                                        type="button" 
                                        wire:click="storeInDrawer" 
                                        class="btn btn-ghost btn-sm w-full text-amber-700 dark:text-amber-400 hover:bg-amber-500/10 text-xs font-bold flex items-center justify-center gap-1.5" 
                                        spinner="storeInDrawer">
                                        <x-mary-icon name="o-archive-box-arrow-down" class="w-4 h-4" />
                                        <span>O guardar directo en gaveta ({{ $expedient->currentLocation?->short_label ?? 'Estante' }})</span>
                                    </button>
                                </div>
                            @endcan
                        @else
                            {{-- Vista para el OPERADOR: Información de custodia con opción de recepción si se lo trajeron al almacén --}}
                            <div class="space-y-2.5">
                                <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-800 dark:text-amber-300 font-medium">
                                    ⚠️ El expediente está en manos de <strong>{{ $activeLoan?->requester?->name ?? 'el solicitante' }}</strong>. El usuario debe entregarlo en el mostrador del Encargado para cerrar el préstamo.
                                </div>
                                @can('loans.return')
                                    <button 
                                        type="button" 
                                        wire:click="receiveReturn" 
                                        class="btn btn-primary w-full h-11 rounded-xl font-black uppercase text-xs tracking-wider shadow-md flex items-center justify-center gap-2" 
                                        spinner="receiveReturn">
                                        <x-mary-icon name="o-check-badge" class="w-4 h-4" />
                                        <span>Recibir Devolución Físicamente</span>
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </div>
                @endif

                {{-- CASO 2: Expediente Disponible con Solicitud Aprobada en espera --}}
                @if($approvedLoan && $expedient->current_status !== \App\Enums\ExpedientStatus::Loaned)
                    <div class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-indigo-500/5 border border-indigo-500/20 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                                <x-mary-icon name="o-hand-raised" class="w-4 h-4" />
                            </div>
                            <div class="text-xs">
                                <p class="font-black text-slate-900 dark:text-white leading-tight">Solicitud Aprobada Lista para Entrega</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Para: <strong class="text-slate-800 dark:text-slate-200">{{ $approvedLoan->requester?->name }}</strong>
                                </p>
                            </div>
                        </div>

                        @can('loans.deliver')
                            <button 
                                type="button" 
                                wire:click="quickDeliver" 
                                class="btn btn-primary w-full h-12 rounded-xl font-black uppercase text-xs tracking-wider shadow-md flex items-center justify-center gap-2" 
                                spinner="quickDeliver">
                                <x-mary-icon name="o-arrow-up-tray" class="w-4 h-4" />
                                <span>Entregar al Solicitante</span>
                            </button>
                        @endcan
                    </div>
                @endif

                {{-- CASO 3: Expediente Devuelto (En mostrador / Pendiente de archivar) --}}
                @if($expedient->current_status === \App\Enums\ExpedientStatus::Returned)
                    <div class="p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl bg-violet-500/5 border border-violet-500/20 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0">
                                <x-mary-icon name="o-arrow-path" class="w-4 h-4" />
                            </div>
                            <div class="text-xs">
                                <p class="font-black text-slate-900 dark:text-white leading-tight">Expediente Devuelto (En Mostrador)</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    @if($this->isOperator)
                                        Listo para reingresar a su gaveta: <strong>{{ $expedient->currentLocation?->full_label ?? 'Gaveta Oficial' }}</strong>
                                    @else
                                        El solicitante ya lo devolvió. Pendiente de que el operador lo coloque en gaveta.
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Acción Reina del Operador: Guardar en Gaveta --}}
                        @canany(['loans.return', 'expedients.change-location'])
                            <button 
                                type="button" 
                                wire:click="storeInDrawer" 
                                class="btn btn-primary w-full h-12 rounded-xl font-black uppercase text-xs tracking-wider shadow-md flex items-center justify-center gap-2" 
                                spinner="storeInDrawer">
                                <x-mary-icon name="o-archive-box-arrow-down" class="w-4 h-4" />
                                <span>Confirmar Guardado en Gaveta Oficial</span>
                            </button>
                        @endcanany
                    </div>
                @endif

                {{-- Enlaces secundarios móviles según el Rol --}}
                <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-2 pt-2 border-t border-slate-100 dark:border-white/10">
                    <a href="{{ route('expedients.show', $expedient) }}" class="btn btn-ghost btn-sm text-[11px] font-bold text-slate-600 dark:text-slate-300 rounded-xl justify-center">
                        <x-mary-icon name="o-eye" class="w-3.5 h-3.5 mr-1" />
                        <span>Ver Ficha</span>
                    </a>

                    <a href="{{ route('expedients.print', $expedient) }}" target="_blank" class="btn btn-ghost btn-sm text-[11px] font-bold text-slate-600 dark:text-slate-300 rounded-xl justify-center">
                        <x-mary-icon name="o-printer" class="w-3.5 h-3.5 mr-1" />
                        <span>Etiqueta</span>
                    </a>
                </div>

            </div>
        @else
            {{-- Empty / Waiting State --}}
            <div class="py-8 sm:py-10 text-center text-slate-400">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center mx-auto mb-2.5 text-slate-400 dark:text-slate-500">
                    <x-mary-icon name="o-qr-code" class="w-6 h-6 sm:w-7 sm:h-7" />
                </div>
                <h4 class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Esperando código...</h4>
                <p class="text-[11px] sm:text-xs text-slate-400 max-w-xs mx-auto">Apunta con la cámara o pistola al código de barras o QR.</p>
            </div>
        @endif

        {{-- Modal Actions Footer --}}
        <x-slot:actions>
            <div class="flex items-center justify-between w-full pt-3 border-t border-slate-100 dark:border-white/10 gap-2">
                @if($expedient)
                    <button type="button" wire:click="resetState" class="btn btn-ghost btn-sm rounded-xl text-xs font-bold text-slate-500 h-9">
                        <x-mary-icon name="o-arrow-path" class="w-3.5 h-3.5 mr-1" />
                        <span>Otro</span>
                    </button>
                @else
                    <div></div>
                @endif
                <button type="button" wire:click="closeScanner" class="btn btn-ghost btn-sm rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 h-9">
                    Cerrar
                </button>
            </div>
        </x-slot:actions>

    </x-mary-modal>
</div>
