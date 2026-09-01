<div>
    <x-mary-header title="Nuevo Expediente" subtitle="Búsqueda en sistema de nómina o captura manual y vinculación física" separator />

    <div class="max-w-3xl">
        <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">Vinculación de Expediente</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary">Sincronización con nómina o captura de nuevo ingreso</p>
                    </div>
                    <x-mary-button 
                        label="+ Capturar Nuevo Empleado" 
                        icon="o-user-plus" 
                        wire:click="openManualModal" 
                        class="btn-outline btn-primary rounded-xl font-black text-xs uppercase tracking-wider h-11 shrink-0" 
                    />
                </div>

                <x-mary-form wire:submit="save">
                    <div class="space-y-10">
                        <!-- Buscador Personalizado -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">1. Buscar Empleado (Nómina o Base Local)</label>
                            </div>
                            
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                                    <x-mary-icon name="o-magnifying-glass" class="w-5 h-5" />
                                </div>
                                <input 
                                    type="text"
                                    placeholder="Escribe RFC, No. Empleado o Nombre..." 
                                    wire:model.live.debounce.300ms="searchEmployee"
                                    @focus="open = true"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-6 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none"
                                />

                                @if(!empty($searchResults))
                                    <div 
                                        x-show="open" 
                                        class="absolute z-50 w-full mt-3 bg-white dark:bg-slate-900 shadow-2xl shadow-primary/20 border border-slate-100 dark:border-white/5 rounded-[2rem] max-h-80 overflow-y-auto p-2 animate-in fade-in slide-in-from-top-2 duration-300"
                                    >
                                        @foreach($searchResults as $result)
                                            <button 
                                                type="button"
                                                wire:click="selectEmployee('{{ $result['id'] }}', '{{ $result['source'] }}', {{ $result['local_id'] ?? 'null' }})"
                                                @click="open = false"
                                                class="w-full text-left px-6 py-4 hover:bg-primary hover:text-white rounded-2xl transition-premium group/item mb-1 flex items-center justify-between"
                                            >
                                                <div>
                                                    <div class="font-black text-slate-800 dark:text-slate-100 group-hover/item:text-white flex items-center gap-2">
                                                        <span>{{ $result['name'] }}</span>
                                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md {{ $result['source'] === 'local' ? 'bg-indigo-500/10 text-indigo-600 group-hover/item:bg-white/20 group-hover/item:text-white' : 'bg-amber-500/10 text-amber-600 group-hover/item:bg-white/20 group-hover/item:text-white' }}">
                                                            {{ $result['source'] === 'local' ? 'Base Local' : 'Nómina API' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-[10px] font-black uppercase tracking-widest opacity-50 group-hover/item:opacity-100 mt-1">
                                                        RFC: {{ $result['rfc'] }}
                                                    </div>
                                                </div>
                                                <div class="text-[10px] font-black bg-slate-50 dark:bg-white/5 group-hover/item:bg-white/20 px-3 py-1 rounded-lg">
                                                    #{{ $result['employee_number'] }}
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(strlen($searchEmployee) >= 3)
                                    <div x-show="open" class="absolute z-50 w-full mt-3 bg-white dark:bg-slate-900 shadow-2xl p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 text-center">
                                        <p class="text-sm font-bold text-slate-600 dark:text-slate-300">No se encontró ningún empleado con ese criterio.</p>
                                        <p class="text-xs text-slate-400 mt-1">¿Es un nuevo ingreso no registrado en nómina?</p>
                                        <x-mary-button label="Capturar Empleado Manualmente" icon="o-user-plus" wire:click="openManualModal" class="btn-primary btn-sm mt-4 font-black uppercase tracking-wider rounded-xl" />
                                    </div>
                                @endif
                            </div>

                            <!-- Indicador de Selección -->
                            @if($employee_id)
                                <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl flex items-center justify-between animate-in zoom-in-95 duration-300">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/30">
                                            <x-mary-icon name="o-check-circle" class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Empleado Vinculado</p>
                                            <p class="text-[10px] font-bold text-emerald-600/70">{{ $searchEmployee }}</p>
                                        </div>
                                    </div>
                                    <x-mary-button label="CAMBIAR" wire:click="$set('employee_id', null)" class="btn-ghost btn-xs font-black text-emerald-700 hover:bg-emerald-100 rounded-lg" />
                                </div>
                            @endif
                        </div>

                        <div class="relative py-4">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-slate-100"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="bg-white px-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Asignación de Almacén</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">2. Ubicación Física en Archivo</label>
                                @if($isAutoSuggested)
                                    <span class="text-[10px] font-black uppercase tracking-wider bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-2.5 py-1 rounded-lg border border-indigo-500/20 flex items-center gap-1 animate-pulse">
                                        <x-mary-icon name="o-sparkles" class="w-3.5 h-3.5" />
                                        Sugerencia Automática por Apellido
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Paso 2.1: Archivero / Mueble -->
                                <div>
                                    <x-mary-select 
                                        label="Paso 1: Archivero / Gaveta" 
                                        wire:model.live="selectedCabinet" 
                                        :options="$cabinets" 
                                        placeholder="Selecciona Archivero..." 
                                        icon="o-building-office" 
                                    />
                                </div>

                                <!-- Paso 2.2: Cajón / Nivel -->
                                <div>
                                    <x-mary-select 
                                        label="Paso 2: Cajón y Rango" 
                                        wire:model="location_id" 
                                        :options="$drawers" 
                                        placeholder="{{ empty($selectedCabinet) ? 'Primero selecciona un archivero...' : 'Selecciona un cajón...' }}" 
                                        :disabled="empty($selectedCabinet)" 
                                        icon="o-inbox-stack" 
                                    />
                                </div>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 pl-1">El código de barras y QR se generarán automáticamente vinculados a este cajón.</p>
                        </div>
                    </div>

                    <x-slot:actions>
                        <div class="flex gap-4 w-full mt-12">
                            <x-mary-button label="Cancelar" link="{{ route('expedients.index') }}" class="btn-ghost flex-1 rounded-2xl h-14 font-black uppercase text-xs tracking-widest" />
                            <x-mary-button label="Generar Expediente" type="submit" icon="o-rocket-launch" class="btn-primary flex-[2] rounded-2xl h-14 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/20 border-none" spinner="save" />
                        </div>
                    </x-slot:actions>
                </x-mary-form>
            </div>
        </x-mary-card>
    </div>

    <!-- Modal de Captura Manual de Empleado -->
    <x-mary-modal wire:model="showManualModal" class="p-8 modal-wide">
        <div class="flex items-center justify-between gap-4 mb-8 border-b border-slate-100 pb-6">
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Captura de Empleado (Nuevo Ingreso)</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Registrar trabajador que aún no figura en la nómina central</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center font-black">
                <x-mary-icon name="o-user-plus" class="w-6 h-6" />
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-mary-input label="RFC (10 caracteres) *" wire:model="manual_rfc" placeholder="Ej: GOMA850215" icon="o-identification" class="uppercase" maxlength="10" />
                </div>
                <div>
                    <x-mary-input label="No. de Empleado (Opcional)" wire:model="manual_employee_number" placeholder="Ej: 10452" icon="o-hashtag" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-mary-input label="Nombre(s) *" wire:model="manual_first_name" placeholder="Nombre(s)..." icon="o-user" />
                </div>
                <div>
                    <x-mary-input label="Apellidos *" wire:model="manual_last_name" placeholder="Apellidos..." icon="o-user" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full justify-end pt-6">
                <x-mary-button label="Cancelar" wire:click="$set('showManualModal', false)" class="btn-ghost rounded-xl font-bold" />
                <x-mary-button label="Guardar y Vincular" wire:click="saveManualEmployee" class="btn-primary rounded-xl px-8 font-black uppercase text-xs tracking-wider shadow-lg shadow-primary/20" spinner="saveManualEmployee" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>

