<div 
    x-data="{
        html5QrCode: null,
        isScanning: false,
        isPaused: false,
        cameraError: null,
        hasFlash: false,
        flashOn: false,
        online: navigator.onLine,
        scanCooldown: false,
        cooldownPercent: 0,
        showHistory: false,

        init() {
            window.addEventListener('online', () => this.online = true);
            window.addEventListener('offline', () => this.online = false);

            this.$nextTick(() => {
                this.startCamera();
            });

            // Escuchar eventos de Livewire para feedback sensorial y reactivación
            window.addEventListener('scan-success', (e) => {
                const data = e.detail[0] || e.detail;
                this.triggerFeedback(true, data.sound, data.vibrate);
                if (data.autoNext) {
                    this.startCooldown(1400);
                }
            });

            window.addEventListener('scan-error', (e) => {
                const data = e.detail[0] || e.detail;
                this.triggerFeedback(false, data.sound, data.vibrate);
                this.startCooldown(2000);
            });

            window.addEventListener('resume-scanner', () => {
                this.resumeScanning();
            });
        },

        async startCamera() {
            this.cameraError = null;
            if (typeof Html5Qrcode === 'undefined') {
                this.cameraError = 'Librería de cámara cargando...';
                setTimeout(() => this.startCamera(), 500);
                return;
            }

            try {
                if (this.html5QrCode) {
                    await this.html5QrCode.stop().catch(() => {});
                }

                const formats = (typeof Html5QrcodeSupportedFormats !== 'undefined')
                    ? [
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.UPC_A
                    ]
                    : undefined;

                this.html5QrCode = new Html5Qrcode('pwa-camera-viewport', formats ? { formatsToSupport: formats, verbose: false } : undefined);

                const config = {
                    fps: 22,
                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                        const w = Math.floor(viewfinderWidth * 0.90);
                        const h = Math.floor(Math.min(viewfinderHeight * 0.65, 220));
                        return { width: Math.max(w, 260), height: Math.max(h, 140) };
                    },
                    aspectRatio: 1.333333,
                    videoConstraints: {
                        facingMode: { ideal: 'environment' },
                        focusMode: 'continuous'
                    }
                };

                await this.html5QrCode.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        this.onCodeDetected(decodedText);
                    },
                    () => {}
                );

                this.isScanning = true;
                this.isPaused = false;

                // Verificar soporte de linterna/flash
                try {
                    const track = this.html5QrCode.getRunningTrackCameraCapabilities();
                    if (track && track.torchFeature && track.torchFeature().isSupported()) {
                        this.hasFlash = true;
                    }
                } catch(e) {}

            } catch (err) {
                console.error('Error al iniciar cámara:', err);
                this.cameraError = 'No se pudo activar la cámara. Revisa los permisos de tu navegador o prueba recargar.';
                this.isScanning = false;
            }
        },

        async toggleFlash() {
            if (!this.hasFlash || !this.html5QrCode) return;
            try {
                this.flashOn = !this.flashOn;
                await this.html5QrCode.applyVideoConstraints({
                    advanced: [{ torch: this.flashOn }]
                });
            } catch(e) {
                console.warn('Flash no disponible:', e);
            }
        },

        togglePause() {
            if (this.isPaused) {
                this.resumeScanning();
            } else {
                this.pauseScanning();
            }
        },

        pauseScanning() {
            if (this.html5QrCode && this.isScanning) {
                this.html5QrCode.pause(true);
                this.isPaused = true;
            }
        },

        resumeScanning() {
            if (this.html5QrCode) {
                try {
                    this.html5QrCode.resume();
                } catch(e) {}
                this.isPaused = false;
                this.scanCooldown = false;
            }
        },

        onCodeDetected(decodedText) {
            if (this.scanCooldown || this.isPaused) return;

            // Pausar temporalmente mientras Laravel procesa
            this.scanCooldown = true;
            if (this.html5QrCode) {
                try { this.html5QrCode.pause(true); } catch(e) {}
            }

            // Enviar a Livewire
            $wire.processCode(decodedText);
        },

        triggerFeedback(isSuccess, sound, vibrate) {
            // Sonido sintético Web Audio
            if (sound && window.playAudioTone) {
                window.playAudioTone(isSuccess ? 'success' : 'error');
            }
            // Vibración háptica
            if (vibrate && navigator.vibrate) {
                navigator.vibrate(isSuccess ? [50, 40, 50] : [180, 60, 180]);
            }
        },

        startCooldown(ms) {
            this.scanCooldown = true;
            this.cooldownPercent = 100;
            const step = 50;
            const decrement = (step / ms) * 100;

            const timer = setInterval(() => {
                this.cooldownPercent -= decrement;
                if (this.cooldownPercent <= 0) {
                    clearInterval(timer);
                    this.cooldownPercent = 0;
                    this.resumeScanning();
                }
            }, step);
        }
    }"
    class="min-h-screen flex flex-col bg-slate-950 text-slate-100 relative select-none">

    {{-- BARRA SUPERIOR ULTRA-COMPACTA (PWA HEADER) --}}
    <header class="sticky top-0 z-40 bg-[#0F1E36]/90 backdrop-blur-md border-b border-white/10 px-4 py-2.5 safe-pt flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('dashboard') }}" class="p-1 -ml-1 text-slate-400 hover:text-white rounded-lg transition-colors" title="Volver al Panel">
                <x-mary-icon name="o-arrow-left" class="w-5 h-5" />
            </a>
            <img src="{{ asset('60issste.png') }}" alt="Logo" class="h-6 w-auto object-contain" />
            <div>
                <h1 class="text-xs font-black tracking-tight text-white leading-none">
                    Escáner Móvil <span class="text-[#C4A462]">PWA</span>
                </h1>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span 
                        class="w-2 h-2 rounded-full transition-colors"
                        :class="online ? 'bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]' : 'bg-rose-500 shadow-[0_0_6px_rgba(244,63,94,0.8)]'"></span>
                    <span class="text-[9px] font-bold text-slate-300" x-text="online ? 'En Línea (Intranet)' : 'Sin Conexión'"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            {{-- Botón de Silenciar / Activar Audio --}}
            <button 
                type="button" 
                wire:click="toggleSound" 
                class="btn btn-ghost btn-circle btn-xs text-slate-300 hover:text-white"
                title="Sonido de Escaneo">
                <x-mary-icon :name="$soundEnabled ? 'o-speaker-wave' : 'o-speaker-x-mark'" class="w-4 h-4" />
            </button>

            {{-- Historial de Escaneos Toggle --}}
            <button 
                type="button" 
                @click="showHistory = !showHistory" 
                class="btn btn-ghost btn-circle btn-xs text-slate-300 hover:text-white relative"
                title="Historial de Sesión">
                <x-mary-icon name="o-clock" class="w-4 h-4" />
                @if(count($scanHistory) > 0)
                    <span class="absolute -top-1 -right-1 bg-[#C4A462] text-[#0F1E36] font-black text-[8px] w-3.5 h-3.5 rounded-full flex items-center justify-center">
                        {{ count($scanHistory) }}
                    </span>
                @endif
            </button>
        </div>
    </header>

    {{-- SELECTOR DE MODO DE ESCANEO --}}
    <nav class="bg-slate-900/90 border-b border-white/5 px-3 py-1.5 flex items-center justify-between gap-1.5 text-xs">
        <button 
            type="button" 
            wire:click="setScannerMode('interactive')"
            class="flex-1 py-1.5 px-2 rounded-lg font-black text-[10px] uppercase tracking-wider transition-all flex items-center justify-center gap-1 cursor-pointer {{ $scannerMode === 'interactive' ? 'bg-[#C4A462] text-[#0F1E36] shadow-sm' : 'text-slate-400 hover:bg-white/5' }}">
            <x-mary-icon name="o-bolt" class="w-3 h-3" />
            <span>Rápido</span>
        </button>

        <button 
            type="button" 
            wire:click="setScannerMode('auto-return')"
            class="flex-1 py-1.5 px-2 rounded-lg font-black text-[10px] uppercase tracking-wider transition-all flex items-center justify-center gap-1 cursor-pointer {{ $scannerMode === 'auto-return' ? 'bg-emerald-500 text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:bg-white/5' }}">
            <x-mary-icon name="o-arrow-path" class="w-3 h-3" />
            <span>Auto-Devolver</span>
            @if($autoReturnsCount > 0)
                <span class="ml-1 bg-black/40 text-white px-1.5 rounded-full text-[9px]">{{ $autoReturnsCount }}</span>
            @endif
        </button>

        <button 
            type="button" 
            wire:click="setScannerMode('inquiry')"
            class="flex-1 py-1.5 px-2 rounded-lg font-black text-[10px] uppercase tracking-wider transition-all flex items-center justify-center gap-1 cursor-pointer {{ $scannerMode === 'inquiry' ? 'bg-sky-500 text-white shadow-sm' : 'text-slate-400 hover:bg-white/5' }}">
            <x-mary-icon name="o-magnifying-glass" class="w-3 h-3" />
            <span>Consulta</span>
        </button>
    </nav>

    {{-- ÁREA PRINCIPAL: VISOR DE CÁMARA (Ocupa la mayor parte de la pantalla) --}}
    <main class="flex-1 flex flex-col relative bg-black">
        
        {{-- Contenedor del Viewfinder --}}
        <div class="relative w-full h-[52vh] sm:h-[58vh] bg-black overflow-hidden flex items-center justify-center">
            
            <div id="pwa-camera-viewport" class="w-full h-full object-cover"></div>

            {{-- Mensaje de Error si no hay cámara --}}
            <template x-if="cameraError">
                <div class="absolute inset-x-6 p-4 rounded-2xl bg-rose-950/90 border border-rose-500/50 text-center text-rose-200 text-xs shadow-2xl backdrop-blur-md">
                    <x-mary-icon name="o-exclamation-triangle" class="w-8 h-8 text-rose-400 mx-auto mb-2" />
                    <p class="font-bold" x-text="cameraError"></p>
                    <button type="button" @click="startCamera()" class="mt-3 btn btn-xs btn-error text-white font-black uppercase tracking-wider">
                        Reintentar Cámara
                    </button>
                </div>
            </template>

            {{-- OVERLAY DE RETÍCULA Y GUÍA LÁSER --}}
            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                {{-- Retícula de enfoque --}}
                <div 
                    class="w-[88%] max-w-[340px] h-[170px] sm:h-[200px] border-2 rounded-2xl transition-all duration-300 relative flex items-center justify-center"
                    :class="{
                        'border-emerald-400 shadow-[0_0_20px_rgba(52,211,153,0.6)]': scanCooldown && '{{ $statusType }}' === 'success',
                        'border-rose-500 shadow-[0_0_20px_rgba(244,63,94,0.6)]': scanCooldown && '{{ $statusType }}' === 'error',
                        'border-[#C4A462]/70 shadow-[0_0_12px_rgba(196,164,98,0.3)]': !scanCooldown && !isPaused,
                        'border-slate-600 opacity-40': isPaused
                    }">
                    
                    {{-- Línea láser guía animada --}}
                    <div 
                        x-show="!isPaused && !scanCooldown"
                        class="absolute inset-x-2 h-[2px] bg-red-500/90 shadow-[0_0_8px_rgba(239,68,68,0.9)] animate-pulse top-1/2 -translate-y-1/2">
                    </div>

                    {{-- Esquinas reforzadas estilo HUD táctil --}}
                    <span class="absolute -top-1 -left-1 w-3.5 h-3.5 border-t-2 border-l-2 border-[#C4A462]"></span>
                    <span class="absolute -top-1 -right-1 w-3.5 h-3.5 border-t-2 border-r-2 border-[#C4A462]"></span>
                    <span class="absolute -bottom-1 -left-1 w-3.5 h-3.5 border-b-2 border-l-2 border-[#C4A462]"></span>
                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 border-b-2 border-r-2 border-[#C4A462]"></span>
                </div>

                {{-- Estado debajo de la retícula --}}
                <div class="mt-3 pointer-events-none text-center">
                    <span 
                        x-show="!scanCooldown && !isPaused"
                        class="px-3 py-1 bg-black/75 text-slate-200 text-[10px] font-black uppercase tracking-wider rounded-full backdrop-blur-md border border-white/10 shadow-lg">
                        🎯 Alinea código QR o de barras
                    </span>
                    <span 
                        x-show="scanCooldown"
                        class="px-3 py-1 bg-emerald-500/90 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-full shadow-lg animate-pulse"
                        x-cloak>
                        ⚡ Procesado • Listo para el siguiente
                    </span>
                    <span 
                        x-show="isPaused"
                        class="px-3 py-1 bg-amber-500/90 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-full shadow-lg"
                        x-cloak>
                        ⏸ Cámara Pausada
                    </span>
                </div>
            </div>

            {{-- BOTONES FLOTANTES SOBRE EL LENTE (Flash & Pausa) --}}
            <div class="absolute bottom-3 right-3 flex items-center gap-2 z-20">
                {{-- Toggle Linterna / Flash --}}
                <template x-if="hasFlash">
                    <button 
                        type="button" 
                        @click="toggleFlash()"
                        class="w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-md border border-white/20 transition-all shadow-lg cursor-pointer"
                        :class="flashOn ? 'bg-amber-400 text-slate-950' : 'bg-black/60 text-white'">
                        <x-mary-icon name="o-bolt" class="w-5 h-5" />
                    </button>
                </template>

                {{-- Toggle Pausa / Reanudar --}}
                <button 
                    type="button" 
                    @click="togglePause()"
                    class="w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-md border border-white/20 transition-all shadow-lg cursor-pointer"
                    :class="isPaused ? 'bg-emerald-500 text-slate-950' : 'bg-black/60 text-white'">
                    <span x-show="isPaused"><x-mary-icon name="o-play" class="w-5 h-5" /></span>
                    <span x-show="!isPaused"><x-mary-icon name="o-pause" class="w-5 h-5" /></span>
                </button>
            </div>

            {{-- BARRA DE CUENTA REGRESIVA PARA SIGUIENTE ESCANEO (Debounce visual) --}}
            <div 
                x-show="scanCooldown"
                class="absolute bottom-0 inset-x-0 h-1 bg-white/10 overflow-hidden"
                x-cloak>
                <div 
                    class="h-full bg-[#C4A462] transition-all duration-75 ease-linear"
                    :style="'width: ' + cooldownPercent + '%'"></div>
            </div>
        </div>

        {{-- SECCIÓN INFERIOR: RESULTADO, FICHA DEL EXPEDIENTE Y ACCIONES TÁCTILES --}}
        <section class="flex-1 bg-slate-900 border-t border-white/10 p-3 safe-pb overflow-y-auto">
            
            {{-- BANNER DE ESTADO INMEDIATO --}}
            @if($statusMessage)
                <div class="mb-3 p-3 rounded-xl flex items-center gap-2.5 text-xs font-bold transition-all shadow-md animate-in fade-in slide-in-from-bottom-2 {{ $statusType === 'success' ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : ($statusType === 'error' ? 'bg-rose-500/15 border border-rose-500/30 text-rose-300' : 'bg-sky-500/15 border border-sky-500/30 text-sky-300') }}">
                    <x-mary-icon :name="$statusType === 'success' ? 'o-check-circle' : ($statusType === 'error' ? 'o-x-circle' : 'o-information-circle')" class="w-5 h-5 shrink-0" />
                    <div class="flex-1 leading-snug">
                        {{ $statusMessage }}
                    </div>
                    @if($statusType === 'error')
                        <button type="button" wire:click="clearCurrent" class="btn btn-ghost btn-xs btn-circle text-rose-400">
                            <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            @endif

            {{-- TARJETA DEL EXPEDIENTE DETECTADO --}}
            @if($currentExpedient)
                <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-3.5 shadow-xl space-y-3 animate-in zoom-in-95 duration-150">
                    
                    {{-- Encabezado del Expediente --}}
                    <div class="flex items-start justify-between gap-2 border-b border-white/10 pb-2.5">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-black text-sm text-white tracking-tight">
                                    {{ $currentExpedient->expedient_code }}
                                </span>
                                <x-mary-badge :value="$currentExpedient->current_status->label()" class="badge-{{ $currentExpedient->current_status->color() }} font-bold text-[10px]" />
                            </div>
                            <div class="text-xs font-bold text-slate-300 mt-0.5">
                                {{ $currentExpedient->employee?->full_name ?? 'Sin titular asignado' }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                RFC: <span class="font-mono text-slate-200">{{ $currentExpedient->employee?->rfc ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-[9px] font-black uppercase tracking-wider text-[#C4A462]">
                                Ubicación Oficial
                            </div>
                            <div class="text-xs font-black text-white">
                                {{ $currentExpedient->currentLocation?->short_label ?? 'Sin Gaveta' }}
                            </div>
                        </div>
                    </div>

                    {{-- DETALLE DEL PRÉSTAMO SI ESTÁ PRESTADO --}}
                    @if($activeLoan)
                        <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-200 text-xs flex items-center justify-between">
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-amber-400 block">En Préstamo Con:</span>
                                <span class="font-bold">{{ $activeLoan->requester?->name ?? 'Usuario' }}</span>
                            </div>
                            <span class="text-[9px] font-bold text-amber-300/80">{{ $activeLoan->created_at->diffForHumans() }}</span>
                        </div>
                    @endif

                    {{-- BOTONES DE ACCIÓN SEGÚN ROL --}}
                    <div class="space-y-2 pt-1">
                        
                        {{-- Flujo de Encargado: Recibir en Mostrador --}}
                        @if($currentExpedient->current_status === \App\Enums\ExpedientStatus::Loaned && $activeLoan && $this->isEncargado)
                            <button 
                                type="button" 
                                wire:click="receiveReturn"
                                class="btn btn-warning w-full font-black text-xs uppercase tracking-wider h-11 rounded-xl shadow-lg gap-2 text-slate-950">
                                <x-mary-icon name="o-inbox-arrow-down" class="w-4 h-4" />
                                <span>📥 Registrar Recepción de Devolución</span>
                            </button>
                        @endif

                        {{-- Flujo de Operador: Guardar en Gaveta --}}
                        @if(($currentExpedient->current_status === \App\Enums\ExpedientStatus::Returned || $currentExpedient->current_status === \App\Enums\ExpedientStatus::Loaned) && $this->isOperator)
                            <button 
                                type="button" 
                                wire:click="storeInDrawer"
                                class="btn btn-success w-full font-black text-xs uppercase tracking-wider h-11 rounded-xl shadow-lg gap-2 text-slate-950">
                                <x-mary-icon name="o-archive-box" class="w-4 h-4" />
                                <span>🗄️ Confirmar Guardado en Gaveta Oficial</span>
                            </button>
                        @endif

                        {{-- Flujo de Entrega si hay préstamo autorizado --}}
                        @if($pendingLoan && $currentExpedient->current_status === \App\Enums\ExpedientStatus::Available && Auth::user()->can('loans.deliver'))
                            <button 
                                type="button" 
                                wire:click="quickDeliver"
                                class="btn btn-primary w-full font-black text-xs uppercase tracking-wider h-11 rounded-xl shadow-lg gap-2 text-white">
                                <x-mary-icon name="o-paper-airplane" class="w-4 h-4" />
                                <span>📤 Entregar a {{ $pendingLoan->requester?->name }}</span>
                            </button>
                        @endif

                        {{-- Botón para Siguiente Escaneo --}}
                        <button 
                            type="button" 
                            wire:click="clearCurrent"
                            class="btn btn-ghost border border-white/10 w-full font-bold text-xs uppercase tracking-wider h-9 rounded-xl text-slate-300 hover:text-white">
                            <span>Siguiente Escaneo (Continuar)</span>
                        </button>
                    </div>
                </div>

            @elseif(!$statusMessage)
                {{-- ESTADO VACÍO / ESPERANDO ESCANEO --}}
                <div class="text-center py-6 text-slate-400">
                    <x-mary-icon name="o-qr-code" class="w-10 h-10 mx-auto mb-2 text-slate-600" />
                    <p class="text-xs font-bold text-slate-300">Apunta la cámara a una etiqueta</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">El sistema detectará automáticamente el código sin recargar la pantalla.</p>
                </div>
            @endif

        </section>
    </main>

    {{-- MODAL / SLIDEOVER DE HISTORIAL DE SESIÓN --}}
    <div 
        x-show="showHistory" 
        x-cloak 
        class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex flex-col justify-end">
        <div 
            @click.away="showHistory = false"
            class="bg-slate-900 border-t border-white/10 rounded-t-3xl max-h-[70vh] flex flex-col p-4 shadow-2xl safe-pb animate-in slide-in-from-bottom duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-clock" class="w-5 h-5 text-[#C4A462]" />
                    <h3 class="text-sm font-black text-white">Historial de la Sesión</h3>
                </div>
                <button type="button" @click="showHistory = false" class="btn btn-ghost btn-circle btn-xs text-slate-400">
                    <x-mary-icon name="o-x-mark" class="w-5 h-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-white/5 my-2">
                @forelse($scanHistory as $item)
                    <div class="py-2.5 flex items-center justify-between gap-2 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-white">{{ $item['code'] }}</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-bold {{ $item['success'] ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    {{ $item['status'] }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 truncate">{{ $item['name'] }}</div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500 shrink-0">{{ $item['time'] }}</span>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-xs">
                        No hay lecturas registradas en esta sesión.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
