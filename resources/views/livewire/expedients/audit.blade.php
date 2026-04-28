<div>
    <x-mary-header title="Auditoría de Inventario" subtitle="Verifica la consistencia física de un estante o gaveta" separator>
        <x-slot:actions>
            @if($is_auditing)
                <x-mary-button label="Nueva Auditoría" icon="o-arrow-path" wire:click="resetAudit" class="btn-ghost" />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Panel de Control -->
        <div class="space-y-8">
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                <div class="p-4 space-y-6">
                    <div class="flex flex-col gap-1 mb-4">
                        <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">Configuración</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary">Paso 1: Seleccione ubicación</p>
                    </div>

                    <div class="space-y-4">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">Ubicación a auditar</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                                <x-mary-icon name="o-map-pin" class="w-5 h-5" />
                            </div>
                            <select 
                                wire:model="location_id"
                                @if($is_auditing) disabled @endif
                                class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-10 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 appearance-none outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <option value="">Seleccione ubicación...</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location['id'] }}">{{ $location['full_label'] }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <x-mary-icon name="o-chevron-down" class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                    
                    @if(!$is_auditing)
                        <x-mary-button 
                            label="Comenzar Auditoría" 
                            icon="o-play" 
                            wire:click="startAudit" 
                            class="btn-primary w-full h-14 rounded-2xl shadow-lg shadow-primary/20 mt-2 font-black uppercase text-xs tracking-widest" 
                            spinner="startAudit" />
                    @endif
                </div>
            </x-mary-card>

            @if($is_auditing)
                <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                    <div class="p-4 space-y-6">
                        <div class="flex flex-col gap-1 mb-4">
                            <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">Escaneo</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest text-primary">Paso 2: Procesar Códigos</p>
                        </div>

                        <div id="reader" class="hidden mb-6 rounded-2xl overflow-hidden bg-slate-50 border-4 border-slate-100 shadow-inner" wire:ignore></div>
                        
                        <div class="flex gap-2 mb-6">
                            <x-mary-button label="Usar Cámara" icon="o-camera" id="start-camera" class="btn-primary btn-outline flex-1 rounded-xl h-12" />
                            <x-mary-button label="Detener" icon="o-stop" id="stop-camera" class="btn-error btn-outline hidden flex-1 rounded-xl h-12" />
                        </div>

                        <form wire:submit.prevent="scan" class="flex-1">
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                                    <x-mary-icon name="o-qr-code" class="w-5 h-5" />
                                </div>
                                <input 
                                    id="scan-input"
                                    type="text"
                                    placeholder="Escanear o escribir..." 
                                    wire:model="current_scan" 
                                    autofocus 
                                    autocomplete="off"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-6 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none"
                                />
                            </div>
                            <x-mary-button type="submit" class="hidden" />
                        </form>
                        
                        <div class="mt-8 p-6 bg-primary/5 rounded-[2rem] border border-primary/10 flex flex-col items-center group transition-premium hover:bg-primary/10">
                            <div class="text-5xl font-black text-primary tracking-tighter mb-1">{{ count($scanned_codes) }}</div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Escaneados en sesión</div>
                        </div>
                    </div>
                </x-mary-card>

                <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                    <div class="p-4">
                        <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest mb-6">Resumen de Auditoría</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/5">
                                <span class="text-xs font-bold text-slate-500">Esperados:</span>
                                <span class="text-lg font-black text-slate-800 dark:text-slate-100 tracking-tighter">{{ $expectedCount }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-success/5 rounded-xl border border-success/10">
                                <span class="text-xs font-bold text-success">Correctos:</span>
                                <span class="text-lg font-black text-success tracking-tighter">{{ count($results['correct']) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-warning/5 rounded-xl border border-warning/10">
                                <span class="text-xs font-bold text-warning">Fuera de lugar:</span>
                                <span class="text-lg font-black text-warning tracking-tighter">{{ count($results['misplaced']) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-error/5 rounded-xl border border-error/10">
                                <span class="text-xs font-bold text-error">Faltantes:</span>
                                <span class="text-lg font-black text-error tracking-tighter">{{ count($results['missing']) }}</span>
                            </div>
                        </div>
                    </div>
                </x-mary-card>
            @endif
        </div>

        <!-- Resultados -->
        <div class="lg:col-span-2 space-y-8">
            @if(!$is_auditing)
                <div class="flex flex-col items-center justify-center py-40 bg-slate-50/50 dark:bg-white/5 rounded-[3rem] border-2 border-dashed border-slate-100 dark:border-white/5">
                    <div class="p-8 bg-white dark:bg-slate-800 rounded-[2rem] shadow-sm mb-6">
                        <x-mary-icon name="o-magnifying-glass" class="w-16 h-16 text-slate-300" />
                    </div>
                    <h3 class="font-black text-slate-800 dark:text-slate-100 text-lg">Inicia una Auditoría</h3>
                    <p class="text-sm text-slate-500 mt-2">Selecciona una ubicación física para verificar su consistencia.</p>
                </div>
            @else
                <div class="space-y-8">
                    @if(count($results['misplaced']) > 0)
                        <x-mary-card shadow class="border-none shadow-xl shadow-warning/10 bg-warning/5 overflow-hidden">
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-sm font-black text-warning uppercase tracking-widest">Fuera de Lugar ({{ count($results['misplaced']) }})</h3>
                                    <x-mary-button label="Corregir Todos" icon="o-check-circle" wire:click="fixAllMisplaced" class="btn-xs btn-warning px-4 rounded-lg" spinner="fixAllMisplaced" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($results['misplaced'] as $exp)
                                        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-warning/10 flex justify-between items-center group/item hover:scale-[1.02] transition-premium">
                                            <div>
                                                <div class="font-black text-slate-800 dark:text-slate-100">{{ $exp->expedient_code }}</div>
                                                <div class="text-[9px] font-black text-slate-400 uppercase tracking-wider mt-1">Registrado en: {{ $exp->currentLocation->full_label ?? 'N/A' }}</div>
                                            </div>
                                            <x-mary-button icon="o-map-pin" wire:click="fixMisplaced({{ $exp->id }})" class="btn-xs btn-warning btn-ghost hover:bg-warning/10 rounded-lg opacity-0 group-hover/item:opacity-100 transition-opacity" tooltip="Traer aquí" spinner />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </x-mary-card>
                    @endif

                    @if(count($results['missing']) > 0)
                        <x-mary-card shadow class="border-none shadow-xl shadow-error/10 bg-error/5">
                            <div class="p-4">
                                <h3 class="text-sm font-black text-error uppercase tracking-widest mb-6">Faltantes ({{ count($results['missing']) }})</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($results['missing'] as $exp)
                                        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-error/10">
                                            <div class="font-black text-slate-800 dark:text-slate-100">{{ $exp->expedient_code }}</div>
                                            <div class="text-xs font-bold text-slate-500 mt-1">{{ $exp->employee->full_name }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </x-mary-card>
                    @endif

                    <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                        <div class="p-4">
                            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest mb-6">Confirmados ({{ count($results['correct']) }})</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($results['correct'] as $exp)
                                    <div class="p-3 bg-success/5 rounded-xl border border-success/10 text-center group hover:bg-success/10 transition-colors">
                                        <div class="text-[10px] font-black text-success tracking-tighter">{{ $exp->expedient_code }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-mary-card>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            let html5QrCode = null;
            const startBtn = document.getElementById('start-camera');
            const stopBtn = document.getElementById('stop-camera');
            const readerDiv = document.getElementById('reader');
            const scanInput = document.getElementById('scan-input');

            if (!startBtn) return;

            const startCamera = () => {
                html5QrCode = new Html5Qrcode("reader");
                readerDiv.classList.remove('hidden');
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                
                html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                    // Ponemos el texto en el input y disparamos el evento de Livewire
                    @this.set('current_scan', decodedText);
                    @this.addScan();
                    
                    // Feedback visual rápido
                    scanInput.classList.add('ring-2', 'ring-success');
                    setTimeout(() => scanInput.classList.remove('ring-2', 'ring-success'), 500);
                });
            };

            const stopCamera = () => {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        readerDiv.classList.add('hidden');
                        startBtn.classList.remove('hidden');
                        stopBtn.classList.add('hidden');
                    });
                }
            };

            startBtn.addEventListener('click', startCamera);
            stopBtn.addEventListener('click', stopCamera);
        });
    </script>
    @endpush
</div>
