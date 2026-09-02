<div>
    <x-mary-header title="Nueva Solicitud" subtitle="Selecciona el expediente que requieres en préstamo" class="mb-6 sm:mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost rounded-xl sm:rounded-2xl h-11 sm:h-14 px-4 sm:px-6 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100" link="{{ route('loans.index') }}">
                <span class="hidden sm:inline">Cancelar</span>
                <span class="sm:hidden">Atrás</span>
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Indicador de pasos --}}
    <div class="flex items-center gap-0 mb-8 sm:mb-12 max-w-lg">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $expedient_id ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-primary text-white shadow-lg shadow-primary/20' }} flex items-center justify-center text-[10px] font-black shrink-0">
                @if($expedient_id)
                    <x-mary-icon name="o-check" class="w-3.5 h-3.5" />
                @else
                    1
                @endif
            </div>
            <span class="text-[10px] sm:text-xs font-black uppercase tracking-wider {{ $expedient_id ? 'text-emerald-500' : 'text-primary' }}">
                <span class="hidden sm:inline">Seleccionar expediente</span>
                <span class="sm:hidden">Expediente</span>
            </span>
        </div>
        <div class="flex-1 h-[2px] mx-3 sm:mx-4 {{ $expedient_id ? 'bg-emerald-500/40' : 'bg-slate-200 dark:bg-white/10' }} rounded-full"></div>
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $expedient_id ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center text-[10px] font-black shrink-0">
                2
            </div>
            <span class="text-[10px] sm:text-xs font-black uppercase tracking-wider {{ $expedient_id ? 'text-primary' : 'text-slate-400' }}">
                <span class="hidden sm:inline">Confirmar solicitud</span>
                <span class="sm:hidden">Confirmar</span>
            </span>
        </div>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 sm:gap-8">

            {{-- Panel principal --}}
            <div class="lg:col-span-3 space-y-5 sm:space-y-6">

                {{-- Paso 1: Expediente --}}
                <div class="premium-card p-5 sm:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-9 h-9 rounded-2xl {{ $expedient_id ? 'bg-emerald-500/10 text-emerald-600' : 'bg-primary/10 text-primary' }} flex items-center justify-center shrink-0">
                            <x-mary-icon name="{{ $expedient_id ? 'o-check-circle' : 'o-folder-open' }}" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight">Expediente</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Paso 1 de 2</p>
                        </div>
                    </div>

                    @if($preSelectedExpedient)
                        <div class="flex items-center gap-4 sm:gap-5 p-4 sm:p-5 bg-emerald-500/5 border border-emerald-500/20 rounded-2xl">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center shrink-0">
                                <x-mary-icon name="o-folder" class="w-6 h-6 sm:w-7 sm:h-7 text-emerald-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.2em]">Listo para solicitar</span>
                                <p class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate mt-0.5">
                                    {{ $preSelectedExpedient->expedient_code }}
                                </p>
                                <p class="text-sm font-bold text-slate-500 truncate">
                                    {{ $preSelectedExpedient->employee->first_name }} {{ $preSelectedExpedient->employee->last_name }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2 shrink-0">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                    <x-mary-icon name="o-check" class="w-3.5 h-3.5 text-white" />
                                </div>
                                <button type="button" wire:click="clearExpedient" class="text-[9px] font-black text-slate-400 hover:text-rose-500 uppercase tracking-wider transition-colors">
                                    Cambiar
                                </button>
                            </div>
                        </div>
                        <input type="hidden" wire:model="expedient_id">
                    @else
                        {{-- Buscador custom consistente con el resto del sistema --}}
                        <div x-data="{ open: false }" @click.outside="open = false">
                            <x-mary-input
                                wire:model.live.debounce.300ms="searchExpedient"
                                icon="o-magnifying-glass"
                                placeholder="Código, RFC o nombre del trabajador..."
                                hint="Escribe al menos 2 caracteres para buscar."
                                x-on:input="open = true"
                                x-on:focus="open = ($wire.searchExpedient?.length >= 2)"
                                autocomplete="off"
                            />

                            {{-- Dropdown de resultados --}}
                            <div
                                x-show="open && $wire.searchExpedient?.length >= 2"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="relative z-50 mt-1"
                                style="display: none;"
                            >
                                <div class="absolute w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-2xl shadow-xl shadow-slate-900/10 overflow-hidden">
                                    @if($expedients->isEmpty())
                                        <div class="flex items-center gap-3 px-5 py-4 text-slate-400">
                                            <x-mary-icon name="o-magnifying-glass" class="w-4 h-4 shrink-0" />
                                            <span class="text-xs font-bold">Sin resultados — prueba con el RFC o nombre</span>
                                        </div>
                                    @else
                                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-white/5">
                                            @foreach($expedients as $exp)
                                                <button
                                                    type="button"
                                                    wire:click="selectExpedient({{ $exp->id }})"
                                                    x-on:click="open = false"
                                                    class="w-full flex items-center gap-4 px-5 py-3.5 hover:bg-primary/5 transition-colors text-left group"
                                                >
                                                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-800 group-hover:bg-primary/10 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                                                        <x-mary-icon name="o-folder" class="w-4 h-4 text-slate-400 group-hover:text-primary transition-colors" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-black text-slate-900 dark:text-white group-hover:text-primary transition-colors truncate">
                                                            {{ $exp->expedient_code }}
                                                        </p>
                                                        <p class="text-[11px] font-bold text-slate-500 truncate mt-0.5">
                                                            {{ $exp->employee->full_name ?? ($exp->employee->first_name . ' ' . $exp->employee->last_name) }}
                                                        </p>
                                                    </div>
                                                    <x-mary-icon name="o-arrow-right" class="w-4 h-4 text-slate-300 group-hover:text-primary transition-colors shrink-0" />
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @error('expedient_id')
                            <p class="mt-2 text-xs font-bold text-rose-500 flex items-center gap-1.5">
                                <x-mary-icon name="o-exclamation-circle" class="w-3.5 h-3.5 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    @endif
                </div>

                {{-- Paso 2: Observaciones --}}
                <div class="premium-card p-5 sm:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-9 h-9 rounded-2xl {{ $expedient_id ? 'bg-primary/10 text-primary' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center shrink-0">
                            <x-mary-icon name="o-chat-bubble-left-right" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight">
                                Motivo <span class="text-slate-400 font-bold text-xs">(Opcional)</span>
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Paso 2 de 2</p>
                        </div>
                    </div>

                    <div class="relative">
                        <textarea
                            wire:model="observations"
                            placeholder="Ej. Revisión para auditoría interna, verificación de datos de nómina, proceso de baja..."
                            rows="4"
                            maxlength="500"
                            class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-white/5 rounded-2xl p-4 pr-16 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 focus:bg-white dark:focus:bg-slate-950 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none resize-none text-sm leading-relaxed"
                        ></textarea>
                        <div class="absolute bottom-3 right-4 text-[10px] font-bold text-slate-300 dark:text-slate-600 tabular-nums">
                            {{ strlen($observations) }}/500
                        </div>
                    </div>

                    @error('observations')
                        <p class="mt-2 text-xs font-bold text-rose-500 flex items-center gap-1.5">
                            <x-mary-icon name="o-exclamation-circle" class="w-3.5 h-3.5 shrink-0" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            {{-- Panel derecho --}}
            <div class="lg:col-span-2 space-y-5 sm:space-y-6">

                {{-- Resumen + botón de envío --}}
                <div class="premium-card p-5 sm:p-8 {{ $expedient_id ? 'border-primary/20' : '' }}">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-5">Resumen</h3>

                    <div class="space-y-4 mb-7">
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full mt-0.5 shrink-0 flex items-center justify-center {{ $expedient_id ? 'bg-emerald-500 shadow-sm shadow-emerald-500/30' : 'bg-slate-200 dark:bg-slate-700' }}">
                                @if($expedient_id)
                                    <x-mary-icon name="o-check" class="w-2.5 h-2.5 text-white" />
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-700 dark:text-slate-200">Expediente</p>
                                <p class="text-[10px] font-bold mt-0.5 {{ $expedient_id ? 'text-emerald-600' : 'text-slate-400' }}">
                                    @if($preSelectedExpedient)
                                        {{ $preSelectedExpedient->expedient_code }}
                                    @elseif($expedient_id)
                                        Seleccionado ✓
                                    @else
                                        Pendiente de selección
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full mt-0.5 shrink-0 flex items-center justify-center bg-emerald-500 shadow-sm shadow-emerald-500/30">
                                <x-mary-icon name="o-check" class="w-2.5 h-2.5 text-white" />
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-700 dark:text-slate-200">Solicitante</p>
                                <p class="text-[10px] font-bold text-slate-500 mt-0.5">{{ auth()->user()->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full mt-0.5 shrink-0 flex items-center justify-center bg-emerald-500 shadow-sm shadow-emerald-500/30">
                                <x-mary-icon name="o-check" class="w-2.5 h-2.5 text-white" />
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-700 dark:text-slate-200">Motivo</p>
                                <p class="text-[10px] font-bold text-slate-500 mt-0.5">
                                    {{ $observations ? Str::limit($observations, 45) : 'Sin observaciones' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        @class([
                            'w-full flex items-center justify-center gap-2.5 h-14 rounded-2xl font-black uppercase text-xs tracking-widest transition-premium',
                            'bg-primary text-white shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.99] cursor-pointer' => $expedient_id,
                            'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed' => !$expedient_id,
                        ])
                        @if(!$expedient_id) disabled @endif
                        wire:loading.attr="disabled"
                        wire:target="save"
                    >
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-2.5">
                            <x-mary-icon name="o-paper-airplane" class="w-4 h-4" />
                            Enviar Solicitud
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2.5">
                            <x-mary-icon name="o-arrow-path" class="w-4 h-4 animate-spin" />
                            Enviando...
                        </span>
                    </button>

                    @if(!$expedient_id)
                        <p class="text-[10px] font-bold text-slate-400 text-center mt-3">
                            Selecciona un expediente para continuar
                        </p>
                    @endif
                </div>

                {{-- Flujo del proceso --}}
                <div class="premium-card p-5 sm:p-6">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-5">¿Cómo funciona?</h3>
                    <div class="space-y-5">
                        <div class="flex gap-3.5">
                            <div class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-mary-icon name="o-clock" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-700 dark:text-slate-200">Revisión por RH</p>
                                <p class="text-[10px] font-bold text-slate-400 leading-relaxed mt-0.5">Tu solicitud se envía al Departamento de RH para autorización.</p>
                            </div>
                        </div>
                        <div class="flex gap-3.5">
                            <div class="w-7 h-7 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-mary-icon name="o-check-circle" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-700 dark:text-slate-200">Surtido en archivo</p>
                                <p class="text-[10px] font-bold text-slate-400 leading-relaxed mt-0.5">El archivista en Planta Baja extrae el fólder de su gaveta.</p>
                            </div>
                        </div>
                        <div class="flex gap-3.5">
                            <div class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-mary-icon name="o-hand-raised" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-700 dark:text-slate-200">Entrega en mesa</p>
                                <p class="text-[10px] font-bold text-slate-400 leading-relaxed mt-0.5">Recoges el expediente en la mesa de control de RH.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

