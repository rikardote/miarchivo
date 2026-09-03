<div>
    <x-page-header title="Tablero de Control" subtitle="Resumen general del sistema de archivo" icon="o-chart-pie" class="mb-10" />

    @if($isAdmin)
        <!-- Dashboard ADMINISTRADOR -->
        @if($pendingTransfersCount > 0)
            <div class="mb-10">
                <x-mary-card class="bg-amber-50 border border-amber-200 rounded-3xl p-6">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="p-4 bg-amber-100 rounded-2xl text-amber-600">
                            <x-mary-icon name="o-truck" class="w-8 h-8" />
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-xl font-bold text-amber-900 mb-1">Traslados Pendientes</h3>
                            <p class="text-amber-800 text-sm">Hay <strong>{{ $pendingTransfersCount }}</strong> expedientes listos para ser movidos al almacén de concentración.</p>
                        </div>
                        <x-mary-button label="Iniciar Gestión" icon="o-arrow-right" link="{{ route('expedients.index', ['filter' => 'pending_transfer']) }}" class="btn-warning rounded-xl px-8" />
                    </div>
                </x-mary-card>
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8 sm:mb-16">
            <div class="premium-card p-4 sm:p-6 flex flex-col justify-between">
                <div class="p-2.5 sm:p-3 bg-primary/10 rounded-xl text-primary w-fit mb-3 sm:mb-4">
                    <x-mary-icon name="o-folder" class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <div>
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Total Archivo</div>
                    <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalExpedients) }}</div>
                </div>
            </div>

            <div class="premium-card p-4 sm:p-6 flex flex-col justify-between">
                <div class="p-2.5 sm:p-3 bg-blue-500/10 rounded-xl text-blue-600 w-fit mb-3 sm:mb-4">
                    <x-mary-icon name="o-arrow-path" class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <div>
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Fuera de Estante</div>
                    <div class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $loanedExpedients }}</div>
                </div>
            </div>

            <div class="premium-card p-4 sm:p-6 flex flex-col justify-between">
                <div class="p-2.5 sm:p-3 bg-amber-500/10 rounded-xl text-amber-600 w-fit mb-3 sm:mb-4">
                    <x-mary-icon name="o-clock" class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <div>
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Por Aprobar</div>
                    <div class="text-2xl sm:text-3xl font-bold text-amber-600">{{ $pendingRequests }}</div>
                </div>
            </div>

            <div class="premium-card p-4 sm:p-6 flex flex-col justify-between">
                <div class="p-2.5 sm:p-3 bg-rose-500/10 rounded-xl text-rose-600 w-fit mb-3 sm:mb-4">
                    <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <div>
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Vencimientos</div>
                    <div class="text-2xl sm:text-3xl font-bold text-rose-600">{{ $overdueLoansCount }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-10">
            <div class="lg:col-span-2 space-y-6 sm:space-y-10">
                <x-mary-card class="premium-card p-4 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Estado de Carpetas</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Monitor en tiempo real</p>
                        </div>
                        <x-mary-button icon="o-information-circle" class="btn-ghost btn-circle btn-sm text-primary" wire:click="$set('showGlossary', true)" tooltip="Ver glosario de estados" />
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                        @foreach($statusStats as $stat)
                            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-white/5 transition-premium hover:scale-[1.02]">
                                <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mb-1.5">{{ $stat['label'] }}</div>
                                <div class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">{{ $stat['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>

                <x-mary-card title="Historial Operativo" class="premium-card border-none shadow-2xl p-4 overflow-hidden">
                    <div class="space-y-6 mt-4 max-h-[460px] overflow-y-auto pr-3">
                        @forelse($recentActivities as $activity)
                            <div class="flex gap-4 group">
                                <div class="relative flex flex-col items-center">
                                    <div class="w-9 h-9 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center z-10 border border-slate-100 dark:border-white/5 group-hover:bg-primary group-hover:text-white transition-premium shadow-sm">
                                        <x-mary-icon name="o-bolt" class="w-4 h-4" />
                                    </div>
                                    <div class="absolute top-9 w-[2px] h-full bg-slate-100 dark:bg-slate-800/50 last:hidden"></div>
                                </div>
                                <div class="pb-6 flex-1">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-primary transition-colors">{{ $activity->description }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <div class="w-1.5 h-1.5 bg-[#C4A462] rounded-full"></div>
                                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center">
                                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4 opacity-20">
                                    <x-mary-icon name="o-inbox" class="w-10 h-10" />
                                </div>
                                <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Sin actividad</p>
                            </div>
                        @endforelse
                    </div>
                </x-mary-card>
            </div>

            <div class="space-y-6 sm:space-y-10">
                <div class="premium-card p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 rounded-xl bg-[#0F1E36]/10 dark:bg-[#C4A462]/10 text-[#0F1E36] dark:text-[#C4A462] flex items-center justify-center">
                            <x-mary-icon name="o-bolt" class="w-4 h-4" />
                        </div>
                        <div>
                            <h4 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Atajos de Sistema</h4>
                            <p class="text-[9px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-1">Acciones Rápidas</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-2.5">
                        @canany(['loans.deliver', 'loans.return'])
                            <a href="{{ route('loans.dispatch') }}" class="flex items-center justify-between p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/5 hover:border-[#0F1E36]/30 dark:hover:border-[#C4A462]/40 hover:bg-white dark:hover:bg-slate-800 transition-premium group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <x-mary-icon name="o-truck" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-colors">Despacho (Planta Baja)</span>
                                </div>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] group-hover:translate-x-0.5 transition-all" />
                            </a>

                            <a href="{{ route('loans.picking-list') }}" target="_blank" class="flex items-center justify-between p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/5 hover:border-[#0F1E36]/30 dark:hover:border-[#C4A462]/40 hover:bg-white dark:hover:bg-slate-800 transition-premium group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <x-mary-icon name="o-printer" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-colors">Hoja de Surtido</span>
                                </div>
                                <x-mary-icon name="o-arrow-top-right-on-square" class="w-4 h-4 text-slate-400 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-all" />
                            </a>
                        @endcanany

                        @can('expedients.create')
                            <a href="{{ route('expedients.create') }}" class="flex items-center justify-between p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/5 hover:border-[#0F1E36]/30 dark:hover:border-[#C4A462]/40 hover:bg-white dark:hover:bg-slate-800 transition-premium group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <x-mary-icon name="o-plus" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-colors">Nuevo Expediente</span>
                                </div>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] group-hover:translate-x-0.5 transition-all" />
                            </a>
                        @endcan

                        @can('loans.approve')
                            <a href="{{ route('loans.bulk') }}" class="flex items-center justify-between p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/5 hover:border-[#0F1E36]/30 dark:hover:border-[#C4A462]/40 hover:bg-white dark:hover:bg-slate-800 transition-premium group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <x-mary-icon name="o-rectangle-stack" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-colors">Solicitud Masiva</span>
                                </div>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] group-hover:translate-x-0.5 transition-all" />
                            </a>

                            <a href="{{ route('loans.index') }}" class="flex items-center justify-between p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/5 hover:border-[#0F1E36]/30 dark:hover:border-[#C4A462]/40 hover:bg-white dark:hover:bg-slate-800 transition-premium group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <x-mary-icon name="o-clipboard-document-check" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-colors">Mesa de Control</span>
                                </div>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] group-hover:translate-x-0.5 transition-all" />
                            </a>
                        @endcan

                        @can('expedients.change-location')
                            <a href="{{ route('expedients.scanner') }}" class="flex items-center justify-between p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/5 hover:border-[#0F1E36]/30 dark:hover:border-[#C4A462]/40 hover:bg-white dark:hover:bg-slate-800 transition-premium group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <x-mary-icon name="o-qr-code" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] transition-colors">Escáner Inteligente</span>
                                </div>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-[#0F1E36] dark:group-hover:text-[#C4A462] group-hover:translate-x-0.5 transition-all" />
                            </a>
                        @endcan
                    </div>
                </div>

                @if($overdueLoans->count() > 0)
                    <x-mary-card class="bg-rose-50 border border-rose-200 rounded-3xl p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-rose-100 text-rose-600 rounded-xl">
                                <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl font-bold text-rose-900">Vencidos</span>
                                <span class="text-xs font-bold text-rose-600 uppercase">ACCIÓN INMEDIATA</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            @foreach($overdueLoans as $loan)
                                <div class="p-4 bg-white rounded-2xl border border-rose-100 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $loan->expedient->expedient_code }}</span>
                                            <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[10px] font-bold rounded-lg uppercase">HAZ {{ $loan->due_date->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                            <x-mary-icon name="o-user" class="w-3 h-3" />
                                            <span>{{ $loan->requester->name }}</span>
                                        </div>
                                    </div>
                                    <x-mary-button label="GESTIONAR" icon="o-pencil-square" link="{{ route('loans.manage', $loan) }}" class="btn-outline btn-sm border-rose-200 text-rose-600 hover:bg-rose-50 w-full sm:w-auto" />
                                </div>
                            @endforeach
                        </div>
                    </x-mary-card>
                @endif

            </div>
        </div>

    @else
        <!-- Dashboard USUARIO ESTÁNDAR -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 mb-8 sm:mb-16">
            <div class="premium-card p-5 sm:p-8 bg-primary text-white border-primary">
                <div class="flex items-center gap-3 mb-2 sm:mb-4">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-white/80">Posesión Activa</div>
                </div>
                <div class="text-3xl sm:text-5xl font-bold">{{ $myActiveLoans }}</div>
                <div class="mt-4 sm:mt-6 flex items-center gap-2 text-[10px] font-bold uppercase text-white/90">
                    <x-mary-icon name="o-check-circle" class="w-4 h-4" />
                    <span>Expedientes en tu posesión</span>
                </div>
            </div>

            <div class="premium-card p-5 sm:p-8 flex flex-col justify-center">
                <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1 sm:mb-2">Trámites Pendientes</div>
                <div class="text-3xl sm:text-5xl font-bold text-slate-900 dark:text-white">{{ $myPendingRequests }}</div>
                <div class="mt-4 sm:mt-6 text-[10px] font-bold text-primary uppercase tracking-wider flex items-center gap-2 cursor-pointer hover:underline" onclick="window.location='{{ route('loans.index', ['mine' => 1]) }}'">
                    <span>Ver mis solicitudes</span>
                    <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                </div>
            </div>

            <div class="premium-card p-5 sm:p-8 flex flex-col justify-center">
                <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-rose-500 mb-1 sm:mb-2">Retrasos Detectados</div>
                <div class="text-3xl sm:text-5xl font-bold text-rose-600">{{ $myOverdueLoans }}</div>
                <div class="mt-4 sm:mt-6 text-[10px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-2 cursor-pointer hover:underline" onclick="window.location='{{ route('loans.index', ['mine' => 1]) }}'">
                    <span>Atención urgente</span>
                    <x-mary-icon name="o-exclamation-circle" class="w-4 h-4" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-mary-card class="premium-card p-4 sm:p-8">
                    <div class="py-6 sm:py-12 text-center">
                        <div class="w-16 h-16 sm:w-24 sm:h-24 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-6 sm:mb-8">
                            <x-mary-icon name="o-magnifying-glass-circle" class="w-8 h-8 sm:w-12 sm:h-12 text-primary" />
                        </div>
                        <h3 class="text-2xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-3 sm:mb-6">Gestión Ágil de Expedientes</h3>
                        <p class="text-slate-600 dark:text-slate-300 mb-6 sm:mb-10 max-w-sm mx-auto text-sm sm:text-base">Localiza expedientes de personal y genera solicitudes de préstamo de forma instantánea y segura.</p>
                        <x-mary-button label="Buscar y Solicitar" icon="o-rocket-launch" link="{{ route('expedients.index') }}" class="btn-primary rounded-xl px-8 sm:px-12 w-full sm:w-auto" />
                    </div>
                </x-mary-card>
            </div>

            <x-mary-card title="Actividad Reciente" class="premium-card p-6 overflow-hidden">
                <div class="space-y-6 mt-6 max-h-[380px] overflow-y-auto pr-3">
                    @forelse($recentActivities as $activity)
                        <div class="flex gap-4">
                            <div class="w-2 h-2 rounded-full bg-primary mt-2"></div>
                            <div>
                                <p class="text-sm text-slate-800 dark:text-slate-100">{{ $activity->description }}</p>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
                            <x-mary-icon name="o-clock" class="w-8 h-8 mx-auto mb-2 opacity-50" />
                            <p class="text-xs font-bold uppercase">Sin actividad</p>
                        </div>
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    @endif

    <!-- Glosario de Estados -->
    <x-mary-modal wire:model="showGlossary" title="Glosario Operativo" class="p-6">
        <div class="space-y-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-1 h-1 bg-primary rounded-full"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Interpretación de Estatus</span>
            </div>            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(\App\Enums\ExpedientStatus::cases() as $status)
                    @php
                        $dashGlossaryClasses = match($status) {
                            \App\Enums\ExpedientStatus::Available  => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                            \App\Enums\ExpedientStatus::Requested  => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                            \App\Enums\ExpedientStatus::Reserved   => 'bg-sky-500/10 text-sky-600 border-sky-500/20',
                            \App\Enums\ExpedientStatus::Loaned     => 'bg-primary/10 text-primary border-primary/20',
                            \App\Enums\ExpedientStatus::Returned   => 'bg-violet-500/10 text-violet-600 border-violet-500/20',
                            \App\Enums\ExpedientStatus::Archived   => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                            \App\Enums\ExpedientStatus::InStorage  => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                            \App\Enums\ExpedientStatus::Lost       => 'bg-rose-500/10 text-rose-600 border-rose-500/20',
                            default                                => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                        };
                    @endphp
                    <div class="flex flex-col gap-3 p-6 rounded-[1.5rem] border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-900 transition-premium group">
                        <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase border w-fit group-hover:scale-105 transition-premium {{ $dashGlossaryClasses }}">
                            {{ $status->label() }}
                        </span>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300 leading-relaxed">
                            @switch($status)
                                @case(\App\Enums\ExpedientStatus::Available) Ubicado físicamente en su estante asignado. @break
                                @case(\App\Enums\ExpedientStatus::Requested) En proceso de validación administrativa. @break
                                @case(\App\Enums\ExpedientStatus::Reserved) Validado y listo para ser recogido. @break
                                @case(\App\Enums\ExpedientStatus::Loaned) En posesión física del usuario solicitante. @break
                                @case(\App\Enums\ExpedientStatus::Returned) Pendiente de re-ubicación en estantería. @break
                                @case(\App\Enums\ExpedientStatus::InStorage) En depósito temporal de baja frecuencia. @break
                                @case(\App\Enums\ExpedientStatus::Archived) Enviado a archivo de concentración final. @break
                                @case(\App\Enums\ExpedientStatus::Lost) Sin localización física confirmada. @break
                            @endswitch
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="ENTENDIDO" wire:click="$set('showGlossary', false)" class="btn-primary w-full rounded-2xl h-14 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/20 border-none" />
        </x-slot:actions>
    </x-mary-modal>
</div>
