<div>
    <x-mary-header title="Nuevo Expediente" subtitle="Búsqueda en sistema de nómina y vinculación física" separator />

    <div class="max-w-3xl">
        <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col gap-1 mb-10">
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">Vinculación de Expediente</h3>
                    <p class="text-[10px] font-black uppercase tracking-widest text-primary">Sincronización con sistema de nómina</p>
                </div>

                <x-mary-form wire:submit="save">
                    <div class="space-y-10">
                        <!-- Buscador Personalizado -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500 mb-3 block">1. Buscar Empleado (API)</label>
                            
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                                    <x-mary-icon name="o-magnifying-glass" class="w-5 h-5" />
                                </div>
                                <input 
                                    type="text"
                                    placeholder="RFC, No. Empleado o Nombre..." 
                                    wire:model.live.debounce.300ms="searchEmployee"
                                    @focus="open = true"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-6 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none"
                                />

                                @if(!empty($apiResults))
                                    <div 
                                        x-show="open" 
                                        class="absolute z-50 w-full mt-3 bg-white dark:bg-slate-900 shadow-2xl shadow-primary/20 border border-slate-100 dark:border-white/5 rounded-[2rem] max-h-80 overflow-y-auto p-2 animate-in fade-in slide-in-from-top-2 duration-300"
                                    >
                                        @foreach($apiResults as $result)
                                            <button 
                                                type="button"
                                                wire:click="selectEmployee('{{ $result['id'] }}')"
                                                @click="open = false"
                                                class="w-full text-left px-6 py-4 hover:bg-primary hover:text-white rounded-2xl transition-premium group/item mb-1 flex items-center justify-between"
                                            >
                                                <div>
                                                    <div class="font-black text-slate-800 dark:text-slate-100 group-hover/item:text-white">{{ $result['name'] }}</div>
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
                                            <p class="text-[10px] font-bold text-emerald-600/70">Listo para generar expediente físico</p>
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
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500 block mb-3">2. Ubicación Física</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                                    <x-mary-icon name="o-map-pin" class="w-5 h-5" />
                                </div>
                                <select 
                                    wire:model="location_id"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-10 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 appearance-none outline-none"
                                >
                                    <option value="">Selecciona Gaveta, Caja o Estante...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location['id'] }}">{{ $location['full_label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                    <x-mary-icon name="o-chevron-down" class="w-4 h-4" />
                                </div>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 pl-2">El código de barras se generará automáticamente tras la creación.</p>
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
</div>
