<div>
    <x-mary-header title="Nuevo Expediente" subtitle="Búsqueda en sistema de nómina y vinculación física" separator />

    <div class="max-w-2xl">
        <x-mary-card>
            <x-mary-form wire:submit="save">
                
                <div class="space-y-6">
                    <!-- Buscador Personalizado con Alpine.js -->
                    <div class="form-control" x-data="{ open: false }" @click.away="open = false">
                        <label class="label font-bold">Buscar Empleado (API)</label>
                        
                        <div class="relative">
                            <x-mary-input 
                                placeholder="Escribe RFC, No. Empleado o Nombre..." 
                                wire:model.live.debounce.300ms="searchEmployee"
                                @focus="open = true"
                                icon="o-magnifying-glass"
                                hint="Resultados directos del sistema de nómina"
                            />

                            @if(!empty($apiResults))
                                <div 
                                    x-show="open" 
                                    class="absolute z-50 w-full mt-1 bg-base-100 shadow-2xl border border-base-300 rounded-xl max-h-64 overflow-y-auto"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                >
                                    @foreach($apiResults as $result)
                                        <button 
                                            type="button"
                                            wire:click="selectEmployee('{{ $result['id'] }}')"
                                            @click="open = false"
                                            class="w-full text-left px-4 py-3 hover:bg-primary hover:text-white transition-colors border-b last:border-0 border-base-200 group"
                                        >
                                            <div class="font-bold">{{ $result['name'] }}</div>
                                            <div class="text-xs opacity-70 group-hover:opacity-100 flex justify-between">
                                                <span>RFC: {{ $result['rfc'] }}</span>
                                                <span>#{{ $result['employee_number'] }}</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Indicador de Selección -->
                        @if($employee_id)
                            <div class="mt-2 p-3 bg-success/10 border border-success/30 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-mary-icon name="o-check-circle" class="text-success" />
                                    <span class="text-sm font-medium">Empleado encontrado, listo para asignar expediente</span>
                                </div>
                                <button type="button" wire:click="$set('employee_id', null)" class="btn btn-xs btn-ghost text-error">Cambiar</button>
                            </div>
                        @endif
                    </div>

                    <div class="divider">Ubicación Física</div>

                    <x-mary-select 
                        label="Asignar Ubicación" 
                        wire:model="location_id" 
                        :options="$locations" 
                        option-label="full_label" 
                        placeholder="Selecciona Gaveta o Caja..." 
                        icon="o-map-pin"
                    />
                </div>

                <x-slot:actions>
                    <x-mary-button label="Cancelar" link="{{ route('expedients.index') }}" />
                    <x-mary-button label="Crear Expediente" type="submit" icon="o-check" class="btn-primary" spinner="save" />
                </x-slot:actions>
            </x-mary-form>
        </x-mary-card>
    </div>
</div>
