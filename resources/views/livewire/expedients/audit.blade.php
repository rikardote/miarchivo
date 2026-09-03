<div @if($is_auditing) wire:poll.800ms="checkRemoteGunAuditScans" @endif class="space-y-6" x-data="{
    init() {
        window.addEventListener('audit-remote-gun-beep', (e) => {
            if (window.playAuditScanBeep) window.playAuditScanBeep();
        });
    }
}">
    {{-- HEADER PRINCIPAL --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/60 dark:border-white/5">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-gradient-to-br from-[#0F1E36] to-[#1E3A8A] text-[#C4A462] shadow-md shadow-[#0F1E36]/20">
                    <x-mary-icon name="o-clipboard-document-check" class="w-6 h-6" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Auditoría de Inventario</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Conciliación física en tiempo real de expedientes por gaveta y estantería</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            @if($is_auditing)
                <button 
                    type="button" 
                    wire:click="$set('showNotesModal', true)" 
                    class="btn btn-sm btn-primary font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-primary/20 gap-2 h-10 px-4">
                    <x-mary-icon name="o-document-check" class="w-4 h-4" />
                    <span>Guardar Acta Oficial</span>
                </button>
                <button 
                    type="button" 
                    wire:click="resetAudit" 
                    wire:confirm="¿Estás seguro de salir de esta auditoría? Los escaneos no guardados se descartarán."
                    class="btn btn-sm btn-ghost border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 font-bold text-xs uppercase tracking-wider rounded-xl h-10 px-3">
                    <x-mary-icon name="o-arrow-left" class="w-4 h-4" />
                    <span>Salir</span>
                </button>
            @endif
        </div>
    </div>

    @if(!$is_auditing)
        {{-- ========================================================= --}}
        {{-- VISTA DE SELECCIÓN Y CONFIGURACIÓN (CUANDO NO ESTÁ AUDITANDO) --}}
        {{-- ========================================================= --}}
        <div class="space-y-8">
            {{-- Barra de Filtros en Cascada y Buscador --}}
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-white/5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">1. Localiza el Archivero o Gabinete Físico</h2>
                        <p class="text-xs text-slate-500">Filtra por sede o selecciona un gabinete para ver únicamente sus gavetas</p>
                    </div>
                </div>

                {{-- Filtros: Sede, Tipo y Buscador de Texto --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Sede / Delegación</label>
                        <select wire:model.live="selectedBranch" class="select select-sm w-full rounded-xl bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-white/10 font-bold text-xs">
                            <option value="">-- Todas las Sedes --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Tipo de Archivo</label>
                        <select wire:model.live="selectedType" class="select select-sm w-full rounded-xl bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-white/10 font-bold text-xs">
                            <option value="">-- Todos los Tipos --</option>
                            @foreach($types as $t)
                                <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Búsqueda Rápida</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live="locationSearch" 
                                placeholder="Escribe letra (ej: D-G), cajón o gabinete..." 
                                class="input input-sm w-full rounded-xl bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-white/10 text-xs font-bold pl-8"
                            />
                            <x-mary-icon name="o-magnifying-glass" class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400 pointer-events-none" />
                        </div>
                    </div>
                </div>

                {{-- Píldoras de Selección de Gabinete / Módulo (Filtro Previo) --}}
                @if(count($cabinets) > 0)
                    <div class="pt-3 border-t border-slate-100 dark:border-white/5 space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Filtrar por Gabinete Específico:</span>
                        <div class="flex items-center gap-2 overflow-x-auto py-1">
                            <button 
                                type="button" 
                                wire:click="$set('selectedCabinet', null)" 
                                class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ is_null($selectedCabinet) ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                                Todos ({{ count($locations) }})
                            </button>
                            @foreach($cabinets as $cab)
                                <button 
                                    type="button" 
                                    wire:click="selectCabinet('{{ $cab }}')" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5 {{ $selectedCabinet === $cab ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                                    <x-mary-icon name="o-archive-box" class="w-3.5 h-3.5" />
                                    <span>{{ $cab }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Agrupación Visual de Gavetas por Gabinete --}}
            <div class="space-y-6">
                @forelse($groupedLocations as $cabinetName => $drawers)
                    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-white/5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                    <x-mary-icon name="o-archive-box" class="w-4 h-4" />
                                </div>
                                <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">
                                    {{ $cabinetName }}
                                </h3>
                                <span class="text-xs text-slate-400 font-bold">• {{ count($drawers) }} cajones</span>
                            </div>
                            <span class="text-xs font-bold text-slate-500">
                                <strong>{{ $drawers->sum('expedients_count') }}</strong> expedientes en total
                            </span>
                        </div>

                        {{-- Cuadrícula de Cajones de este Gabinete --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($drawers as $loc)
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-white/5 hover:border-[#C4A462]/50 hover:shadow-sm transition-all flex flex-col justify-between group">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-xs text-slate-900 dark:text-white">
                                                {{ $loc->drawer ? "Cajón {$loc->drawer}" : $loc->cabinet }}
                                            </span>
                                            @if($loc->alpha_range)
                                                <span class="px-2 py-0.5 rounded-md bg-[#0F1E36]/5 dark:bg-white/10 text-[10px] font-black text-[#0F1E36] dark:text-[#C4A462]">
                                                    {{ $loc->alpha_range }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 line-clamp-1">{{ $loc->archive_name }}</p>
                                    </div>

                                    <div class="pt-3 mt-2 border-t border-slate-200/40 dark:border-white/5 flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-400">
                                            📁 {{ $loc->expedients_count ?? 0 }} exp
                                        </span>
                                        <button 
                                            type="button" 
                                            wire:click="selectLocationAndStart({{ $loc->id }})" 
                                            class="btn btn-xs btn-primary rounded-lg font-black uppercase text-[10px] px-2.5 shadow-sm">
                                            Auditar ➔
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-white/10">
                        <x-mary-icon name="o-archive-box-x-mark" class="w-10 h-10 mx-auto mb-2 text-slate-300" />
                        <p class="text-xs font-bold">No se encontraron gavetas o archiveros con los filtros actuales.</p>
                    </div>
                @endforelse
            </div>

            {{-- Historial Reciente de Actas --}}
            @if($pastAudits->isNotEmpty())
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-white/5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Historial de Actas de Auditoría Recientes</h3>
                            <p class="text-xs text-slate-500">Registro histórico persistente de conciliaciones físicas</p>
                        </div>
                        <span class="badge badge-sm badge-ghost font-bold text-[10px]">{{ count($pastAudits) }} registradas</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-white/5">
                                    <th>Fecha y Hora</th>
                                    <th>Ubicación Auditada</th>
                                    <th>Auditor</th>
                                    <th class="text-center">Esperados</th>
                                    <th class="text-center">Correctos</th>
                                    <th class="text-center">Faltantes</th>
                                    <th class="text-center">Incorrectos</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-bold divide-y divide-slate-100 dark:divide-white/5">
                                @foreach($pastAudits as $pa)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                                        <td class="whitespace-nowrap text-slate-500">{{ $pa->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="font-black text-slate-900 dark:text-white">{{ $pa->location?->short_label ?? 'N/A' }}</td>
                                        <td class="text-slate-600 dark:text-slate-300">{{ $pa->user?->name ?? 'N/A' }}</td>
                                        <td class="text-center"><span class="badge badge-ghost badge-sm font-bold">{{ $pa->expected_count }}</span></td>
                                        <td class="text-center"><span class="badge badge-success badge-sm font-black text-slate-950">{{ $pa->correct_count }}</span></td>
                                        <td class="text-center"><span class="badge badge-error badge-sm font-black text-white">{{ $pa->missing_count }}</span></td>
                                        <td class="text-center">
                                            @if($pa->misplaced_count > 0)
                                                <span class="badge badge-warning badge-sm font-black text-slate-950">{{ $pa->misplaced_count }}</span>
                                            @else
                                                <span class="text-slate-400 text-[11px]">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    @else
        {{-- ========================================================= --}}
        {{-- VISTA DE AUDITORÍA ACTIVA (HEADS-UP DISPLAY INTERACTIVO) --}}
        {{-- ========================================================= --}}
        <div class="space-y-6">
            {{-- HERO HUD: Ubicación actual, progreso y contadores --}}
            <div class="p-6 rounded-3xl bg-gradient-to-br from-[#0F1E36] via-[#112240] to-[#0A1526] text-white shadow-xl border border-white/10 space-y-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    {{-- Datos de la gaveta --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-lg bg-[#C4A462] text-[#0F1E36] font-black text-[10px] uppercase tracking-wider shadow-sm">
                                AUDITORÍA EN CURSO
                            </span>
                            <span class="text-xs text-slate-300 font-bold">{{ $currentLocation?->branch?->name }}</span>
                        </div>
                        <h2 class="text-2xl lg:text-3xl font-black text-white tracking-tight">
                            {{ $currentLocation?->full_label }}
                        </h2>
                        <p class="text-xs text-slate-300 font-medium flex items-center gap-2">
                            <span>Archivo: <strong>{{ $currentLocation?->archive_name }}</strong></span>
                            <span>•</span>
                            <span>Rango Oficial: <strong class="text-[#C4A462]">{{ $currentLocation?->alpha_range ?: 'General' }}</strong></span>
                        </p>
                    </div>

                    {{-- Mini Tarjetas de Métricas en Vivo --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Esperados</div>
                            <div class="text-2xl font-black text-white tracking-tight mt-0.5">{{ $expectedCount }}</div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Confirmados</div>
                            <div class="text-2xl font-black text-emerald-300 tracking-tight mt-0.5">{{ count($results['correct']) }}</div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-rose-400">Faltantes</div>
                            <div class="text-2xl font-black text-rose-300 tracking-tight mt-0.5">{{ count($results['missing']) }}</div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-amber-400">Cajón Incorrecto</div>
                            <div class="text-2xl font-black text-amber-300 tracking-tight mt-0.5">{{ count($results['misplaced']) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Barra de Progreso del Cajón --}}
                <div class="space-y-1.5 pt-2 border-t border-white/10">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-300">Progreso de Conciliación Física</span>
                        <span class="text-[#C4A462] font-black">{{ $progressPercentage }}% ({{ count($results['correct']) }} de {{ $expectedCount }} verificados)</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-white/10 overflow-hidden p-0.5">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500 ease-out" style="width: {{ $progressPercentage }}%"></div>
                    </div>
                </div>
            </div>

            {{-- COMANDOS DE ESCANEO Y ENLACE CELULAR --}}
            <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-white/5 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    {{-- Input Principal para Pistola USB o Teclado --}}
                    <form wire:submit.prevent="addScan" class="flex-1">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <x-mary-icon name="o-qr-code" class="w-5 h-5" />
                            </div>
                            <input 
                                id="scan-input"
                                type="text" 
                                placeholder="Escanear con pistola física, escribir código o dar Enter..." 
                                wire:model="current_scan"
                                autofocus
                                autocomplete="off"
                                class="input input-bordered w-full h-12 pl-12 pr-28 rounded-2xl bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-white/10 text-xs font-black tracking-wider focus:border-primary"
                            />
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                <button type="submit" class="btn btn-xs btn-primary rounded-xl font-black uppercase text-[10px] px-3">
                                    Registrar
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Botón para activar webcam local (opcional) --}}
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="$toggle('showCamera')"
                            class="btn btn-sm btn-ghost border border-slate-200 dark:border-white/10 rounded-2xl text-xs font-bold gap-1.5 h-12 px-3.5">
                            <x-mary-icon name="o-camera" class="w-4 h-4 text-slate-500" />
                            <span>{{ $showCamera ? 'Ocultar Cámara PC' : 'Cámara Web PC' }}</span>
                        </button>
                    </div>
                </div>

                {{-- Banner Informativo: Pistola Celular Conectada --}}
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 flex items-center justify-between gap-3 text-xs text-emerald-800 dark:text-emerald-300">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <div class="leading-tight">
                            <strong class="font-black uppercase text-[10px] tracking-wider block">📱 Pistola Celular Enlazada en Vivo</strong>
                            <span class="text-[11px] opacity-90">Abre <code>/scanner</code> en tu teléfono con tu misma cuenta. Cada escaneo que hagas en el archivero se registrará aquí al instante.</span>
                        </div>
                    </div>
                    <span class="badge badge-sm badge-success font-black text-[9px] uppercase px-2 py-0.5 text-slate-950 shrink-0">Canal Activo</span>
                </div>

                {{-- Cámara Web Integrada si el usuario la activa --}}
                @if($showCamera)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-white/10 space-y-3" wire:ignore>
                        <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                            <span>Lector de Cámara Web</span>
                            <div class="flex gap-2">
                                <button type="button" onclick="startAuditCamera()" class="btn btn-xs btn-success text-slate-950 font-black">Iniciar</button>
                                <button type="button" onclick="stopAuditCamera()" class="btn btn-xs btn-ghost text-slate-400">Detener</button>
                            </div>
                        </div>
                        <div id="reader" class="rounded-xl overflow-hidden max-w-md mx-auto"></div>
                    </div>
                @endif
            </div>

            {{-- WORKSPACE DE EXPEDIENTES (TABS INTERACTIVOS) --}}
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-white/5 shadow-sm space-y-4">
                {{-- Navegación de Tabs y Buscador --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-white/5 pb-4">
                    <div class="flex items-center gap-2 overflow-x-auto py-1">
                        {{-- Tab: Faltantes --}}
                        <button 
                            type="button" 
                            wire:click="$set('activeTab', 'missing')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5 {{ $activeTab === 'missing' ? 'bg-rose-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                            <span>⏳ Faltantes por Escanear</span>
                            <span class="badge badge-xs {{ $activeTab === 'missing' ? 'bg-white text-rose-600' : 'badge-ghost' }} font-bold">{{ count($results['missing']) }}</span>
                        </button>

                        {{-- Tab: Confirmados --}}
                        <button 
                            type="button" 
                            wire:click="$set('activeTab', 'correct')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5 {{ $activeTab === 'correct' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                            <span>✅ Confirmados en Sitio</span>
                            <span class="badge badge-xs {{ $activeTab === 'correct' ? 'bg-white text-emerald-700' : 'badge-ghost' }} font-bold">{{ count($results['correct']) }}</span>
                        </button>

                        {{-- Tab: En Cajón Incorrecto --}}
                        <button 
                            type="button" 
                            wire:click="$set('activeTab', 'misplaced')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5 {{ $activeTab === 'misplaced' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                            <span>⚠️ Cajón Incorrecto</span>
                            <span class="badge badge-xs {{ $activeTab === 'misplaced' ? 'bg-slate-950 text-amber-400' : 'badge-ghost' }} font-bold">{{ count($results['misplaced']) }}</span>
                        </button>
                    </div>

                    {{-- Buscador en Vivo dentro de los Resultados --}}
                    <div class="relative w-full sm:w-64">
                        <input 
                            type="text" 
                            wire:model.live="searchFilter" 
                            placeholder="Filtrar por código o nombre..." 
                            class="input input-xs input-bordered w-full rounded-xl bg-slate-50 dark:bg-slate-950 text-xs font-medium pl-8"
                        />
                        <x-mary-icon name="o-magnifying-glass" class="w-3.5 h-3.5 absolute left-2.5 top-2 text-slate-400 pointer-events-none" />
                    </div>
                </div>

                {{-- CONTENIDO DEL TAB SELECCIONADO --}}
                @if($activeTab === 'missing')
                    {{-- TAB: FALTANTES --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-500 font-bold">
                            <span>Expedientes que el sistema tiene registrados en este cajón pero aún no has escaneado:</span>
                            <span>{{ count($results['missing']) }} pendientes</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @php
                                $filteredMissing = collect($results['missing'])->filter(function($e) {
                                    if (empty($this->searchFilter)) return true;
                                    $s = mb_strtolower($this->searchFilter);
                                    return str_contains(mb_strtolower($e->expedient_code), $s) || str_contains(mb_strtolower($e->employee?->full_name ?? ''), $s);
                                });
                            @endphp

                            @forelse($filteredMissing as $exp)
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-white/5 flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <span class="font-black font-mono text-sm text-slate-900 dark:text-white">{{ $exp->expedient_code }}</span>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300 line-clamp-1">{{ $exp->employee?->full_name }}</p>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $exp->employee?->rfc }}</span>
                                    </div>
                                    <span class="badge badge-sm badge-error badge-outline font-black text-[9px] uppercase tracking-wider shrink-0">Pendiente</span>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center text-slate-400">
                                    @if(empty($results['missing']))
                                        <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl inline-block mb-2">
                                            <x-mary-icon name="o-check-badge" class="w-8 h-8" />
                                        </div>
                                        <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">¡Felicidades! Todos los expedientes esperados fueron escaneados en su sitio.</p>
                                    @else
                                        <p class="text-xs font-bold">No hay expedientes faltantes que coincidan con la búsqueda.</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>

                @elseif($activeTab === 'correct')
                    {{-- TAB: CONFIRMADOS --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-500 font-bold">
                            <span>Expedientes escaneados físicamente que coinciden con su registro oficial:</span>
                            <span>{{ count($results['correct']) }} verificados</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @php
                                $filteredCorrect = collect($results['correct'])->filter(function($e) {
                                    if (empty($this->searchFilter)) return true;
                                    $s = mb_strtolower($this->searchFilter);
                                    return str_contains(mb_strtolower($e->expedient_code), $s) || str_contains(mb_strtolower($e->employee?->full_name ?? ''), $s);
                                });
                            @endphp

                            @forelse($filteredCorrect as $exp)
                                <div class="p-3.5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-500/20 flex items-start justify-between gap-3 group">
                                    <div class="space-y-1">
                                        <span class="font-black font-mono text-sm text-emerald-700 dark:text-emerald-400">{{ $exp->expedient_code }}</span>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300 line-clamp-1">{{ $exp->employee?->full_name }}</p>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $exp->employee?->rfc }}</span>
                                    </div>
                                    <button 
                                        type="button" 
                                        wire:click="removeScan('{{ $exp->expedient_code }}')" 
                                        class="btn btn-ghost btn-xs text-slate-400 hover:text-rose-500 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity" 
                                        tooltip="Descartar este escaneo">
                                        <x-mary-icon name="o-trash" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center text-slate-400">
                                    <p class="text-xs font-bold">Aún no has escaneado ningún expediente en este cajón.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                @elseif($activeTab === 'misplaced')
                    {{-- TAB: EN CAJÓN INCORRECTO --}}
                    <div class="space-y-3">
                        <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-800 dark:text-amber-300">
                            <div>
                                <strong class="font-black uppercase tracking-wider block">⚠️ Expedientes Ajenos Encontrados Aquí</strong>
                                <span class="text-[11px] opacity-90">Fueron encontrados físicamente en este cajón, pero su registro oficial corresponde a otra gaveta.</span>
                            </div>
                            @if(count($results['misplaced']) > 0)
                                <button 
                                    type="button" 
                                    wire:click="fixAllMisplaced" 
                                    class="btn btn-xs btn-warning rounded-xl font-black uppercase tracking-wider text-[10px] px-3 shadow-sm shrink-0">
                                    Asignar Todos a Este Cajón
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse($results['misplaced'] as $exp)
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-amber-500/30 shadow-sm flex items-start justify-between gap-4">
                                    <div class="space-y-1.5">
                                        <span class="font-black font-mono text-base text-amber-600 dark:text-amber-400">{{ $exp->expedient_code }}</span>
                                        <p class="text-xs font-black text-slate-800 dark:text-slate-100">{{ $exp->employee?->full_name }}</p>
                                        <div class="text-[11px] font-bold text-slate-500">
                                            📍 Su lugar oficial registrado es: <strong class="text-slate-800 dark:text-white">{{ $exp->currentLocation?->short_label ?? 'Sin ubicación' }}</strong>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                                        <button 
                                            type="button" 
                                            wire:click="fixMisplaced({{ $exp->id }})" 
                                            class="btn btn-xs btn-warning rounded-xl font-black uppercase text-[10px] gap-1 shadow-sm">
                                            <x-mary-icon name="o-arrow-path" class="w-3 h-3" />
                                            <span>Reubicar Aquí</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="removeScan('{{ $exp->expedient_code }}')" 
                                            class="btn btn-ghost btn-xs text-slate-400 hover:text-rose-500 text-[10px]">
                                            Descartar
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center text-slate-400">
                                    <p class="text-xs font-bold">No se detectaron expedientes ajenos en este cajón.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- MODAL: GUARDAR ACTA OFICIAL DE AUDITORÍA --}}
        @if($showNotesModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200">
                <div class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden p-6 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-xl bg-primary/10 text-primary">
                                <x-mary-icon name="o-document-check" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-black text-base text-slate-900 dark:text-white">Generar Acta Oficial de Auditoría</h3>
                                <p class="text-xs text-slate-400">{{ $currentLocation?->short_label }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('showNotesModal', false)" class="btn btn-sm btn-ghost btn-circle text-slate-400">✕</button>
                    </div>

                    {{-- Resumen de Cifras del Acta --}}
                    <div class="grid grid-cols-3 gap-2.5 text-center">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-white/5">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Correctos</span>
                            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ count($results['correct']) }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-white/5">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Faltantes</span>
                            <span class="text-xl font-black text-rose-600 dark:text-rose-400">{{ count($results['missing']) }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-white/5">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Cajón Mal</span>
                            <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ count($results['misplaced']) }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Observaciones / Notas del Acta (Opcional)</label>
                        <textarea 
                            wire:model="audit_notes" 
                            rows="3" 
                            placeholder="Escribe comentarios sobre el estado físico de la gaveta, anomalías detectadas o justificaciones..." 
                            class="textarea textarea-bordered w-full rounded-2xl bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-white/10 text-xs font-medium focus:border-primary"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" wire:click="$set('showNotesModal', false)" class="btn btn-sm btn-ghost rounded-xl font-bold text-xs uppercase">Cancelar</button>
                        <button type="button" wire:click="saveAuditReport" class="btn btn-sm btn-primary rounded-xl font-black text-xs uppercase px-5 shadow-lg shadow-primary/20">
                            Confirmar y Guardar Acta
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- SCRIPTS DE AUDITORÍA (CÁMARA WEB Y SONIDO) --}}
    @push('scripts')
    <script>
        window.html5QrCode = window.html5QrCode || null;
        let isProcessingAuditScan = false;

        function playAuditScanBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.12);
                if (navigator.vibrate) navigator.vibrate(90);
            } catch (e) {}
        }

        window.startAuditCamera = function() {
            const readerDiv = document.getElementById('reader');
            const scanInput = document.getElementById('scan-input');
            if (!readerDiv) return;

            if (window.html5QrCode) {
                window.html5QrCode.stop().catch(() => {});
            }

            window.html5QrCode = new Html5Qrcode("reader");
            const config = { 
                fps: 15, 
                qrbox: { width: 250, height: 250 },
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.CODE_128
                ]
            };
            
            window.html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                if (isProcessingAuditScan) return;
                isProcessingAuditScan = true;

                playAuditScanBeep();

                if (scanInput) {
                    scanInput.value = decodedText;
                    scanInput.classList.add('ring-4', 'ring-emerald-500');
                    setTimeout(() => scanInput.classList.remove('ring-4', 'ring-emerald-500'), 800);
                }

                @this.call('addScan', decodedText).then(() => {
                    setTimeout(() => { isProcessingAuditScan = false; }, 1200);
                }).catch(() => {
                    isProcessingAuditScan = false;
                });
            }).catch(err => {
                console.error("Camera error:", err);
            });
        }

        window.stopAuditCamera = function() {
            if (window.html5QrCode) {
                window.html5QrCode.stop().catch(() => {});
            }
            isProcessingAuditScan = false;
        }

        document.addEventListener('livewire:navigating', () => {
            if (window.html5QrCode) {
                window.html5QrCode.stop().catch(() => {});
            }
        });
    </script>
    @endpush
</div>
