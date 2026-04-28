<div>
    <x-mary-header title="Solicitud Masiva" subtitle="Escanea múltiples expedientes para solicitar préstamo por lote" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('loans.index') }}">Cancelar</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel de Escaneo -->
        <div class="lg:col-span-1 space-y-6">
            <x-mary-card title="Scanner" shadow separator>
                <div class="space-y-4">
                    <p class="text-xs text-gray-500">Haz clic en el campo y comienza a escanear. El sistema detectará automáticamente cada código al presionar Enter.</p>
                    
                    <form wire:submit.prevent="processScan">
                        <x-mary-input 
                            wire:model="scannedCode" 
                            id="bulk-scanner-input"
                            placeholder="Escanear código aquí..." 
                            icon="o-qr-code" 
                            autocomplete="off"
                            autofocus
                        />
                    </form>

                    <div class="pt-4 border-t border-base-200">
                        <x-mary-textarea 
                            label="Observaciones Generales" 
                            wire:model="observations" 
                            placeholder="Ej. Revisión trimestral de expedientes..." 
                            rows="3" 
                        />
                    </div>

                    <div class="pt-4">
                        <x-mary-button 
                            label="Enviar Lote ({{ count(collect($items)->filter(fn($i) => $i['isValid'])) }})" 
                            icon="o-paper-airplane" 
                            class="btn-primary w-full" 
                            wire:click="save" 
                            spinner="save"
                            :disabled="count(collect($items)->filter(fn($i) => $i['isValid'])) === 0"
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            const input = document.getElementById('bulk-scanner-input');
            
            // Re-enfocar el input después de cada acción de Livewire
            Livewire.hook('request', ({ respond }) => {
                respond(() => {
                    setTimeout(() => {
                        input.focus();
                    }, 50);
                });
            });

            // Mantener el foco incluso si se hace clic fuera (opcional, pero útil para escaneo rápido)
            document.addEventListener('click', (e) => {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'BUTTON') {
                    input.focus();
                }
            });
        });
    </script>
</div>
