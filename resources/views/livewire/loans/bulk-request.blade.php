<div wire:poll.5s="syncItems">
    <x-mary-header title="Solicitud Masiva" subtitle="Escanea múltiples expedientes para solicitar préstamo por lote" separator>
        <x-slot:actions>
            <x-mary-button icon="o-trash" class="btn-ghost text-error" wire:click="clearList" confirm="¿Estás seguro de limpiar toda la lista?">Limpiar Lista</x-mary-button>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('loans.index') }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel de Escaneo -->
        <div class="lg:col-span-1 space-y-6">
            <x-mary-card title="Destinatario" shadow separator>
                <div class="space-y-4">
                    <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">Responsable del Préstamo</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                            <x-mary-icon name="o-user" class="w-5 h-5" />
                        </div>
                        <select 
                            wire:model.live="user_id"
                            class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-10 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 appearance-none outline-none"
                        >
                            <option value="">Buscar usuario...</option>
                            @foreach($users as $user)
                                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <x-mary-icon name="o-chevron-down" class="w-4 h-4" />
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 pl-2">A nombre de quién se registrará este lote de expedientes.</p>
                </div>
            </x-mary-card>

            <x-mary-card title="Scanner" shadow separator>
                <div class="space-y-4">
                    <!-- Botón para móviles -->
                    <div class="lg:hidden mb-4">
                        <button onclick="startScanner()" class="btn btn-primary w-full rounded-2xl h-14 shadow-xl shadow-primary/20">
                            <x-mary-icon name="o-camera" class="mr-2" />
                            Abrir Cámara
                        </button>
                    </div>

                    <div id="reader-container" class="hidden mb-4 overflow-hidden rounded-2xl border-4 border-primary/20" wire:ignore>
                        <div id="reader"></div>
                        <button onclick="stopScanner()" class="btn btn-ghost btn-sm w-full">Cerrar Cámara</button>
                    </div>

                    <p class="text-xs text-gray-500">Haz clic en el campo y comienza a escanear. El sistema detectará automáticamente cada código al presionar Enter.</p>
                    
                    <form wire:submit.prevent="processScan" wire:ignore>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                                <x-mary-icon name="o-qr-code" class="w-5 h-5" />
                            </div>
                            <input 
                                id="bulk-scanner-input"
                                type="text"
                                placeholder="Escanear código aquí..." 
                                wire:model="scannedCode" 
                                autocomplete="off"
                                autofocus
                                class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-6 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none"
                            />
                        </div>
                    </form>

                    <div class="pt-4 border-t border-base-200 space-y-3">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">Observaciones Generales</label>
                        <textarea 
                            wire:model.live="observations" 
                            placeholder="Ej. Revisión trimestral de expedientes..." 
                            rows="3"
                            class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl p-5 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none resize-none"
                        ></textarea>
                    </div>

                    <div class="pt-4">
                        <x-mary-button 
                            label="Enviar Lote ({{ count(collect($items)->filter(fn($i) => $i['isValid'])) }})" 
                            icon="o-paper-airplane" 
                            class="btn-primary w-full" 
                            wire:click="save" 
                            spinner="save"
                            :disabled="count(collect($items)->filter(fn($i) => $i['isValid'])) === 0 || !$user_id"
                        />
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Lista de Expedientes -->
        <div class="lg:col-span-2">
            <x-mary-card title="Lista de Carga ({{ count($items) }})" shadow>
                @if(count($items) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Empleado</th>
                                    <th>Estado Actual</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr class="{{ !$item['isValid'] ? 'bg-error/5 text-error opacity-80' : '' }}">
                                        <td class="font-bold font-mono text-xs">{{ $item['code'] }}</td>
                                        <td class="text-xs">{{ $item['employee'] }}</td>
                                        <td>
                                            <x-mary-badge :value="$item['status']" class="badge-{{ $item['status_color'] }} badge-xs" />
                                            @if(!$item['isValid'])
                                                <span class="block text-[10px] font-bold mt-1 uppercase text-error">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            <x-mary-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="removeItem({{ $index }})" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-20 text-center flex flex-col items-center justify-center">
                        <x-mary-icon name="o-inbox" class="w-16 h-16 opacity-10 mb-4" />
                        <p class="text-gray-400">No hay expedientes en la lista. Comienza a escanear.</p>
                    </div>
                @endif
            </x-mary-card>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;
        let isScanning = false;

        function startScanner() {
            document.getElementById('reader-container').classList.remove('hidden');
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 15, qrbox: { width: 250, height: 250 } };

            const onSuccess = (decodedText) => {
                if (isScanning) return; // Evitar ráfagas
                
                isScanning = true;
                console.log("Escaneado masivo:", decodedText);
                
                @this.dispatch('code-scanned', { code: decodedText });
                
                // Pequeña pausa para feedback visual y evitar re-escaneos
                setTimeout(() => { isScanning = false; }, 1500);
            };

            html5QrCode.start({ facingMode: "environment" }, config, onSuccess)
                .catch(err => alert("Error de cámara: " + err));
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    document.getElementById('reader-container').classList.add('hidden');
                });
            }
        }

        document.addEventListener('livewire:initialized', () => {
            const input = document.getElementById('bulk-scanner-input');
            
            Livewire.hook('request', ({ respond }) => {
                respond(() => {
                    setTimeout(() => { if(input) input.focus(); }, 50);
                });
            });

            document.addEventListener('click', (e) => {
                if (input && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'BUTTON') {
                    input.focus();
                }
            });
        });
    </script>
    @endpush
</div>
