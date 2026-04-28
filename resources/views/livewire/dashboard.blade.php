<div>
    <x-mary-header title="Tablero de Control" subtitle="Resumen general del sistema de archivo" class="mb-10" />

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

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
            <div class="premium-card p-6 flex flex-col justify-between">
                <div class="p-3 bg-primary/10 rounded-xl text-primary w-fit mb-4">
                    <x-mary-icon name="o-folder" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 mb-1">Total Archivo</div>
                    <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalExpedients) }}</div>
                </div>
            </div>

            <div class="premium-card p-6 flex flex-col justify-between">
                <div class="p-3 bg-blue-500/10 rounded-xl text-blue-600 w-fit mb-4">
                    <x-mary-icon name="o-arrow-path" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 mb-1">Fuera de Estante</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $loanedExpedients }}</div>
                </div>
            </div>

            <div class="premium-card p-6 flex flex-col justify-between">
                <div class="p-3 bg-amber-500/10 rounded-xl text-amber-600 w-fit mb-4">
                    <x-mary-icon name="o-clock" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 mb-1">Por Aprobar</div>
                    <div class="text-3xl font-bold text-amber-600">{{ $pendingRequests }}</div>
                </div>
            </div>

            <div class="premium-card p-6 flex flex-col justify-between">
                <div class="p-3 bg-rose-500/10 rounded-xl text-rose-600 w-fit mb-4">
                    <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 mb-1">Vencimientos</div>
                    <div class="text-3xl font-bold text-rose-600">{{ $overdueLoansCount }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-10">
                <x-mary-card title="Expedientes por Sede" subtitle="Distribución física proporcional" class="premium-card p-6">
                    <div class="space-y-6 mt-6">
                        @foreach($branchStats as $branch)
                            @php
                                $percentage = $totalEmployees > 0 ? ($branch->employees_count / $totalEmployees) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between mb-2 items-end">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $branch->name }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $branch->employees_count }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 ml-1">Carpetas</span>
                                    </div>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>

                <x-mary-card class="premium-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Estado de Carpetas</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 mt-1">Monitor en tiempo real</p>
                        </div>
                        <x-mary-button icon="o-information-circle" class="btn-ghost btn-circle btn-sm text-primary" @click="$dispatch('open-glossary')" />
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($statusStats as $stat)
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-white/5 transition-premium hover:scale-[1.02]">
                                <div class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mb-2">{{ $stat['label'] }}</div>
                                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stat['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>
            </div>

            <div class="space-y-10">
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
                                        <div class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400">
                                            <x-mary-icon name="o-user" class="w-3 h-3" />
                                            <span>{{ $loan->requester->name }}</span>
                                        </div>
                                    </div>
                                    <x-mary-button label="GESTIONAR" icon="o-pencil-square" link="{{ route('loans.manage', $loan) }}" class="btn-outline btn-sm border-rose-200 text-rose-600 hover:bg-rose-50" />
                                </div>
                            @endforeach
                        </div>
                    </x-mary-card>
                @endif

                <x-mary-card title="Historial Operativo" class="premium-card border-none shadow-2xl p-4">
                    <div class="space-y-8 mt-6">
                        @forelse($recentActivities as $activity)
                            <div class="flex gap-5 group">
                                <div class="relative flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center z-10 border border-slate-100 dark:border-white/5 group-hover:bg-primary group-hover:text-white transition-premium shadow-sm">
                                        <x-mary-icon name="o-bolt" class="w-4 h-4" />
                                    </div>
                                    <div class="absolute top-10 w-[2px] h-full bg-slate-100 dark:bg-slate-800/50 last:hidden"></div>
                                </div>
                                <div class="pb-8">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-tight group-hover:text-primary transition-colors">{{ $activity->description }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
                                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest">{{ $activity->created_at->diffForHumans() }}</span>
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

                <x-mary-card class="bg-slate-900 rounded-3xl p-6 text-white">
                    <h4 class="text-xl font-bold mb-6">Atajos de Sistema</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <x-mary-button label="Nuevo Expediente" icon="o-plus" link="{{ route('expedients.create') }}" class="btn-ghost w-full justify-start text-slate-300 hover:text-white rounded-xl py-4" />
                        <x-mary-button label="Solicitud Masiva" icon="o-rectangle-stack" link="{{ route('loans.bulk') }}" class="btn-ghost w-full justify-start text-slate-300 hover:text-white rounded-xl py-4" />
                        <x-mary-button label="Mesa de Control" icon="o-clipboard-document-check" link="{{ route('loans.index') }}" class="btn-ghost w-full justify-start text-slate-300 hover:text-white rounded-xl py-4" />
                    </div>
                </x-mary-card>
            </div>
        </div>

    @else
        <!-- Dashboard USUARIO ESTÁNDAR -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <div class="premium-card p-8 bg-primary text-white border-primary">
                <div class="flex items-center gap-3 mb-4">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-white/80">Posesión Activa</div>
                </div>
                <div class="text-5xl font-bold">{{ $myActiveLoans }}</div>
                <div class="mt-6 flex items-center gap-2 text-[10px] font-bold uppercase text-white/90">
                    <x-mary-icon name="o-check-circle" class="w-4 h-4" />
                    <span>Expedientes en tu posesión</span>
                </div>
            </div>

            <div class="premium-card p-8 flex flex-col justify-center">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 mb-2">Trámites Pendientes</div>
                <div class="text-5xl font-bold text-slate-900 dark:text-white">{{ $myPendingRequests }}</div>
                <div class="mt-6 text-[10px] font-bold text-primary uppercase tracking-wider flex items-center gap-2 cursor-pointer hover:underline">
                    <span>Ver mis solicitudes</span>
                    <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                </div>
            </div>

            <div class="premium-card p-8 flex flex-col justify-center">
                <div class="text-[10px] font-bold uppercase tracking-wider text-rose-500 mb-2">Retrasos Detectados</div>
                <div class="text-5xl font-bold text-rose-600">{{ $myOverdueLoans }}</div>
                <div class="mt-6 text-[10px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-2 cursor-pointer hover:underline">
                    <span>Atención urgente</span>
                    <x-mary-icon name="o-exclamation-circle" class="w-4 h-4" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-mary-card class="premium-card p-8">
                    <div class="py-12 text-center">
                        <div class="w-24 h-24 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-8">
                            <x-mary-icon name="o-magnifying-glass-circle" class="w-12 h-12 text-primary" />
                        </div>
                        <h3 class="text-4xl font-bold text-slate-900 dark:text-white mb-6">Gestión Ágil de Expedientes</h3>
                        <p class="text-slate-600 dark:text-slate-300 mb-10 max-w-sm mx-auto">Localiza expedientes de personal y genera solicitudes de préstamo de forma instantánea y segura.</p>
                        <x-mary-button label="Iniciar Nueva Solicitud" icon="o-rocket-launch" link="{{ route('loans.index') }}" class="btn-primary rounded-xl px-12" />
                    </div>
                </x-mary-card>
            </div>

            <x-mary-card title="Actividad Reciente" class="premium-card p-6">
                <div class="space-y-6 mt-6">
                    @forelse($recentActivities as $activity)
                        <div class="flex gap-4">
                            <div class="w-2 h-2 rounded-full bg-primary mt-2"></div>
                            <div>
                                <p class="text-sm text-slate-800 dark:text-slate-100">{{ $activity->description }}</p>
                                <span class="text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-500 dark:text-slate-400 dark:text-slate-400">
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
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400 dark:text-slate-400">Interpretación de Estatus</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $glossary = [
                        ['label' => 'Disponible', 'color' => 'emerald', 'desc' => 'Ubicado físicamente en su estante asignado.'],
                        ['label' => 'Solicitado', 'color' => 'amber', 'desc' => 'En proceso de validación administrativa.'],
                        ['label' => 'Reservado', 'color' => 'blue', 'desc' => 'Validado y listo para ser recogido.'],
                        ['label' => 'Prestado', 'color' => 'indigo', 'desc' => 'En posesión física del usuario solicitante.'],
                        ['label' => 'Devuelto', 'color' => 'cyan', 'desc' => 'Pendiente de re-ubicación en estantería.'],
                        ['label' => 'En almacén', 'color' => 'slate', 'desc' => 'En depósito temporal de baja frecuencia.'],
                        ['label' => 'Archivado', 'color' => 'gray', 'desc' => 'Enviado a archivo de concentración final.'],
                        ['label' => 'Extraviado', 'color' => 'rose', 'desc' => 'Sin localización física confirmada.'],
                    ];
                @endphp

                @foreach($glossary as $item)
                    <div class="flex flex-col gap-3 p-6 rounded-[1.5rem] border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-900 transition-premium group">
                        <div class="px-4 py-1.5 bg-{{ $item['color'] }}-500 text-white text-[9px] font-black rounded-xl uppercase w-fit shadow-lg shadow-{{ $item['color'] }}-500/20 group-hover:scale-105 transition-premium">{{ $item['label'] }}</div>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300 dark:text-slate-500 leading-relaxed">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="ENTENDIDO" wire:click="$set('showGlossary', false)" class="btn-primary w-full rounded-2xl h-14 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/20 border-none" />
        </x-slot:actions>
    </x-mary-modal>
</div>
