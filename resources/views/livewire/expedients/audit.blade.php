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
        <div class="space-y-6">
            <x-mary-card title="Configuración" subtitle="Paso 1: Seleccione ubicación">
                <x-mary-select 
                    label="Ubicación a auditar" 
                    wire:model="location_id" 
                    :options="$locations" 
                    option-label="full_label" 
                    placeholder="Seleccione..." 
                    :disabled="$is_auditing" />
                
                @if(!$is_auditing)
                    <x-mary-button label="Comenzar Auditoría" icon="o-play" wire:click="startAudit" class="btn-primary w-full mt-4" spinner="startAudit" />
                @endif
            </x-mary-card>

            @if($is_auditing)
                <x-mary-card title="Escaneo" subtitle="Paso 2: Escanee códigos">
                    <div id="reader" class="hidden mb-4 rounded-xl overflow-hidden bg-base-200 border-2 border-dashed border-base-300"></div>
                    
                    <div class="flex gap-2 mb-4">
                        <x-mary-button label="Usar Cámara" icon="o-camera" id="start-camera" class="btn-sm btn-outline flex-1" />
                        <x-mary-button label="Detener" icon="o-stop" id="stop-camera" class="btn-sm btn-error btn-outline hidden" />
                    </div>

                    <form wire:submit.prevent="addScan">
                        <x-mary-input 
                            id="scan-input"
                            wire:model="current_scan" 
                            placeholder="Escanee o escriba código..." 
                            autofocus 
                            autocomplete="off" />
                        <x-mary-button type="submit" class="hidden" />
                    </form>
                    
                    <div class="mt-4 p-4 bg-primary/5 rounded-lg border border-primary/10 text-center">
                        <div class="text-3xl font-black text-primary">{{ count($scanned_codes) }}</div>
                        <div class="text-[10px] uppercase font-bold text-gray-500">Escaneados en esta sesión</div>
                    </div>
                </x-mary-card>

                <x-mary-card title="Resumen" separator>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span>Esperados en esta ubicación:</span>
                            <span class="font-bold">{{ $expectedCount }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-success">
                            <span>Correctos:</span>
                            <span class="font-bold">{{ count($results['correct']) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-warning">
                            <span>Fuera de lugar:</span>
                            <span class="font-bold">{{ count($results['misplaced']) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-error">
                            <span>Faltantes:</span>
                            <span class="font-bold">{{ count($results['missing']) }}</span>
                        </div>
                    </div>
                </x-mary-card>
            @endif
        </div>

        <!-- Resultados -->
        <div class="lg:col-span-2 space-y-6">
            @if(!$is_auditing)
                <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <x-mary-icon name="o-magnifying-glass" class="w-20 h-20 mb-4 opacity-20" />
                    <p>Seleccione una ubicación para iniciar el proceso de verificación.</p>
                </div>
            @else
                <!-- Pestañas de resultados -->
                <div class="space-y-4">
                    @if(count($results['misplaced']) > 0)
                        <x-mary-card title="Fuera de Lugar ({{ count($results['misplaced']) }})" class="border-l-4 border-warning shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($results['misplaced'] as $exp)
                                    <div class="p-2 bg-warning/5 rounded border border-warning/10 text-xs">
                                        <div class="font-bold">{{ $exp->expedient_code }}</div>
                                        <div class="text-gray-500">Debería estar en: {{ $exp->currentLocation->full_label ?? 'Sin ubicación' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </x-mary-card>
                    @endif

                    @if(count($results['missing']) > 0)
                        <x-mary-card title="Faltantes ({{ count($results['missing']) }})" class="border-l-4 border-error shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($results['missing'] as $exp)
                                    <div class="p-2 bg-error/5 rounded border border-error/10 text-xs">
                                        <div class="font-bold">{{ $exp->expedient_code }}</div>
                                        <div class="text-gray-500">{{ $exp->employee->full_name }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </x-mary-card>
                    @endif

                    <x-mary-card title="Correctos ({{ count($results['correct']) }})" class="border-l-4 border-success shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            @foreach($results['correct'] as $exp)
                                <div class="p-2 bg-success/5 rounded border border-success/10 text-[10px] text-center">
                                    <div class="font-bold">{{ $exp->expedient_code }}</div>
                                </div>
                            @endforeach
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
