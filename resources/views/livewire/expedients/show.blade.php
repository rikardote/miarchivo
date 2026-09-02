<div>
    <x-mary-header title="{{ $expedient->expedient_code }}" subtitle="Detalles y movimientos físicos" separator class="mb-4 sm:mb-6">
        <x-slot:actions>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <x-mary-button icon="o-arrow-left" class="btn-ghost btn-sm sm:btn-md" link="{{ route('expedients.index') }}">Volver</x-mary-button>
                @can('loans.create')
                    @if($expedient->isAvailable())
                        <x-mary-button icon="o-document-text" class="btn-secondary btn-sm sm:btn-md" link="{{ route('loans.request', ['expedient' => $expedient->id]) }}">Solicitar</x-mary-button>
                    @else
                        <x-mary-button icon="o-document-text" class="btn-secondary btn-sm sm:btn-md" disabled label="No Disponible" />
                    @endif
                @endcan
                @can('update', $expedient)
                    @if($expedient->current_status->value !== 'lost')
                        <x-mary-button icon="o-exclamation-triangle" class="btn-error btn-outline btn-sm sm:btn-md" label="Extraviado" wire:click="markAsLost" />
                    @else
                        <x-mary-button icon="o-check-circle" class="btn-success btn-outline btn-sm sm:btn-md" label="Recuperado" wire:click="markAsFound" />
                    @endif
                    <x-mary-button icon="o-pencil" class="btn-primary btn-sm sm:btn-md" link="{{ route('expedients.edit', $expedient) }}">Editar</x-mary-button>
                @endcan
            </div>
        </x-slot:actions>
    </x-mary-header>

    <!-- PANEL DE CONTROL OPERATIVO / LOCALIZACIÓN RÁPIDA (Sección 15) -->
    <div class="mb-8 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-2xl relative overflow-hidden border border-slate-700/50">
        <!-- Resplandor decorativo -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <!-- Barra Superior: Identificador y Estatus Operativo -->
            <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-white/10">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-primary">Ficha de Consulta Rápida</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-white/10 text-slate-300">Tomo {{ $expedient->volume_number }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight mt-1">{{ $expedient->expedient_code }}</h1>
                </div>

                @php
                    $statusConfig = match($expedient->current_status) {
                        \App\Enums\ExpedientStatus::Available => [
                            'bg' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                            'dot' => 'bg-emerald-400 animate-pulse',
                            'desc' => 'En estantería física • Disponible para préstamo'
                        ],
                        \App\Enums\ExpedientStatus::Loaned => [
                            'bg' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                            'dot' => 'bg-amber-400 animate-pulse',
                            'desc' => 'Fuera de sala • En préstamo activo'
                        ],
                        \App\Enums\ExpedientStatus::Requested => [
                            'bg' => 'bg-sky-500/20 text-sky-300 border-sky-500/40',
                            'dot' => 'bg-sky-400',
                            'desc' => 'Solicitud pendiente de entrega'
                        ],
                        \App\Enums\ExpedientStatus::Lost => [
                            'bg' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                            'dot' => 'bg-rose-400 animate-ping',
                            'desc' => 'Alerta: Reportado como extraviado'
                        ],
                        default => [
                            'bg' => 'bg-slate-500/20 text-slate-300 border-slate-500/40',
                            'dot' => 'bg-slate-400',
                            'desc' => 'En resguardo institucional'
                        ],
                    };
                @endphp
                <div class="text-left sm:text-right">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border {{ $statusConfig['bg'] }} font-black text-sm uppercase tracking-wider">
                        <span class="w-2.5 h-2.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                        {{ $expedient->current_status->label() }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1 font-bold">{{ $statusConfig['desc'] }}</p>
                </div>
            </div>

            <!-- 4 Respuestas Operativas Inmediatas -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 pt-6">
                <!-- 1. ¿De quién es? -->
                <div class="bg-white/5 hover:bg-white/[0.08] transition-colors rounded-2xl p-4 border border-white/5">
                    <div class="flex items-center gap-2 text-slate-400 mb-2">
                        <x-mary-icon name="o-user" class="w-4 h-4 text-primary" />
                        <span class="text-[10px] font-black uppercase tracking-wider">¿De quién es?</span>
                    </div>
                    <p class="text-base font-black text-white truncate uppercase">{{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}</p>
                    <div class="mt-2 space-y-1 text-xs">
                        <p class="text-slate-300 font-bold flex justify-between">
                            <span class="text-slate-400 font-normal">RFC:</span>
                            <span class="font-mono text-primary font-black">{{ $expedient->employee->rfc }}</span>
                        </p>
                        <p class="text-slate-300 font-bold flex justify-between">
                            <span class="text-slate-400 font-normal">No. Empleado:</span>
                            <span>{{ $expedient->employee->employee_number ?? 'S/N' }}</span>
                        </p>
                        <p class="text-slate-400 text-[11px] truncate mt-1">
                            {{ $expedient->employee->position ?? 'Personal Operativo' }}
                        </p>
                    </div>
                </div>

                <!-- 2. ¿Dónde está físicamente? -->
                <div class="bg-white/5 hover:bg-white/[0.08] transition-colors rounded-2xl p-4 border border-white/5">
                    <div class="flex items-center gap-2 text-slate-400 mb-2">
                        <x-mary-icon name="o-map-pin" class="w-4 h-4 text-emerald-400" />
                        <span class="text-[10px] font-black uppercase tracking-wider">¿Dónde está?</span>
                    </div>
                    @if($expedient->currentLocation)
                        <p class="text-base font-black text-white leading-tight">
                            {{ $expedient->currentLocation->archive_name }}
                        </p>
                        <div class="mt-2 space-y-1 text-xs">
                            <p class="text-slate-300 font-bold flex justify-between">
                                <span class="text-slate-400 font-normal">Gaveta / Cajón:</span>
                                <span class="text-emerald-300 font-black">G-{{ $expedient->currentLocation->cabinet }} • Cajón {{ $expedient->currentLocation->drawer }}</span>
                            </p>
                            @if($expedient->currentLocation->alpha_range)
                                <p class="text-slate-300 font-bold flex justify-between">
                                    <span class="text-slate-400 font-normal">Rango:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-white/10 text-emerald-200">{{ $expedient->currentLocation->alpha_range }}</span>
                                </p>
                            @endif
                            <p class="text-slate-400 text-[11px] truncate mt-1">
                                {{ $expedient->currentLocation->branch->name ?? 'Delegación Estatal' }}
                            </p>
                        </div>
                    @else
                        <p class="text-base font-black text-amber-400">Sin ubicación asignada</p>
                        <p class="text-xs text-slate-400 mt-2">No se ha asignado a ningún archivero físico.</p>
                    @endif
                </div>

                <!-- 3. ¿Quién lo tiene y cuándo regresa? -->
                <div class="bg-white/5 hover:bg-white/[0.08] transition-colors rounded-2xl p-4 border border-white/5">
                    <div class="flex items-center gap-2 text-slate-400 mb-2">
                        <x-mary-icon name="o-shield-check" class="w-4 h-4 text-amber-400" />
                        <span class="text-[10px] font-black uppercase tracking-wider">Custodia / Préstamo</span>
                    </div>
                    @php
                        $activeLoan = $expedient->activeLoan();
                    @endphp
                    @if($expedient->currentHolder)
                        <p class="text-base font-black text-white truncate">{{ $expedient->currentHolder->name }}</p>
                        <div class="mt-2 space-y-1 text-xs">
                            @if($activeLoan && $activeLoan->delivered_at)
                                <p class="text-slate-300 font-bold flex justify-between">
                                    <span class="text-slate-400 font-normal">Salió:</span>
                                    <span>{{ $activeLoan->delivered_at->format('d/m/Y') }}</span>
                                </p>
                            @endif
                            @if($activeLoan && $activeLoan->due_date)
                                @php
                                    $isOverdue = $activeLoan->due_date->isPast();
                                    $daysDiff = abs((int) now()->diffInDays($activeLoan->due_date, false));
                                @endphp
                                <p class="font-bold flex justify-between {{ $isOverdue ? 'text-rose-400' : 'text-slate-300' }}">
                                    <span class="text-slate-400 font-normal">Vence:</span>
                                    <span>{{ $activeLoan->due_date->format('d/m/Y') }}</span>
                                </p>
                                <p class="text-[11px] font-black {{ $isOverdue ? 'text-rose-400' : 'text-emerald-400' }}">
                                    {{ $isOverdue ? "¡Vencido hace {$daysDiff} día(s)!" : "Resta(n) {$daysDiff} día(s)" }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-base font-black text-emerald-400">En resguardo</p>
                        <p class="text-xs text-slate-300 mt-2">Bajo custodia directa del Archivo Central.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Sin préstamo activo en curso.</p>
                    @endif
                </div>

                <!-- 4. Último Movimiento y Auditoría -->
                <div class="bg-white/5 hover:bg-white/[0.08] transition-colors rounded-2xl p-4 border border-white/5">
                    <div class="flex items-center gap-2 text-slate-400 mb-2">
                        <x-mary-icon name="o-clock" class="w-4 h-4 text-violet-400" />
                        <span class="text-[10px] font-black uppercase tracking-wider">Última Trazabilidad</span>
                    </div>
                    @php
                        $lastMovement = $expedient->movements->first();
                        $lastAudit = $expedient->currentLocation?->latestAudit;
                    @endphp
                    @if($lastMovement)
                        <p class="text-sm font-black text-white leading-tight">
                            {{ $lastMovement->movement_type->label() }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            {{ $lastMovement->created_at->diffForHumans() }} ({{ $lastMovement->created_at->format('d/m H:i') }})
                        </p>
                        <p class="text-xs text-slate-300 mt-1">Por: {{ $lastMovement->user->name ?? 'Sistema' }}</p>
                    @else
                        <p class="text-sm font-bold text-slate-400">Sin movimientos registrados</p>
                    @endif

                    @if($lastAudit)
                        <div class="mt-2 pt-2 border-t border-white/10 text-[10px] text-slate-400">
                            <span>Auditoría de cajón:</span>
                            <span class="text-slate-300 font-bold ml-1">{{ $lastAudit->created_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        
        <!-- Info Principal -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
            <!-- Información del Empleado -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-2 sm:p-4 space-y-4 sm:space-y-6">
                    <h3 class="text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100 mb-4 sm:mb-6">Información del Empleado</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8">
                        <div class="flex items-center gap-4 sm:gap-5 group">
                            <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-user" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Nombre Completo</p>
                                <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 sm:gap-5 group">
                            <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-identification" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">RFC</p>
                                <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100">{{ $expedient->employee->rfc }}</p>
                            </div>
                        </div>

                        @if($expedient->employee->employee_number)
                            <div class="flex items-center gap-4 sm:gap-5 group">
                                <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                    <x-mary-icon name="o-hashtag" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                                </div>
                                <div>
                                    <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">No. Empleado</p>
                                    <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                        {{ $expedient->employee->employee_number }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($expedient->employee->position)
                            <div class="flex items-center gap-4 sm:gap-5 group">
                                <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                    <x-mary-icon name="o-briefcase" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                                </div>
                                <div>
                                    <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Puesto / Plaza</p>
                                    <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                        {{ $expedient->employee->position }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($expedient->employee->work_center)
                            <div class="flex items-center gap-4 sm:gap-5 group">
                                <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                    <x-mary-icon name="o-building-office" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                                </div>
                                <div>
                                    <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Centro de Trabajo</p>
                                    <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                        {{ $expedient->employee->work_center }} ({{ $expedient->employee->city ?? 'B.C.' }})
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-4 sm:gap-5 group">
                            <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-building-storefront" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Sucursal</p>
                                <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $expedient->employee->branch->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            <!-- Detalles del Archivo -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-2 sm:p-4 space-y-4 sm:space-y-6">
                    <h3 class="text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100 mb-4 sm:mb-6">Detalles del Archivo</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8">
                        <div class="flex items-center gap-4 sm:gap-5 group">
                            <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-qr-code" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Código</p>
                                <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100">{{ $expedient->expedient_code }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 sm:gap-5 group">
                            <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-book-open" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Tomo</p>
                                <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100">{{ $expedient->volume_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 sm:gap-5 group sm:col-span-2">
                            <div class="p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-map-pin" class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Ubicación Física</p>
                                <p class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100">
                                    {{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 sm:gap-5 group pt-4 border-t border-slate-100 sm:col-span-2">
                            @php
                                $showStatusIcon = match($expedient->current_status) {
                                    \App\Enums\ExpedientStatus::Available  => 'bg-emerald-500/10 text-emerald-600',
                                    \App\Enums\ExpedientStatus::Requested  => 'bg-amber-500/10 text-amber-600',
                                    \App\Enums\ExpedientStatus::Reserved   => 'bg-sky-500/10 text-sky-600',
                                    \App\Enums\ExpedientStatus::Loaned     => 'bg-primary/10 text-primary',
                                    \App\Enums\ExpedientStatus::Returned   => 'bg-violet-500/10 text-violet-600',
                                    \App\Enums\ExpedientStatus::Archived   => 'bg-slate-500/10 text-slate-600',
                                    \App\Enums\ExpedientStatus::InStorage  => 'bg-indigo-500/10 text-indigo-600',
                                    \App\Enums\ExpedientStatus::Lost       => 'bg-rose-500/10 text-rose-600',
                                    default                                => 'bg-slate-500/10 text-slate-500',
                                };
                            @endphp
                            <div class="p-2.5 sm:p-3 {{ $showStatusIcon }} rounded-2xl">
                                <x-mary-icon name="o-tag" class="w-6 h-6 sm:w-7 sm:h-7" />
                            </div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Estado</p>
                                <p class="text-lg sm:text-xl font-black {{ explode(' ', $showStatusIcon)[1] }}">
                                    {{ $expedient->current_status->label() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Sidebar Detalles -->
        <div class="space-y-8">
            <!-- Identificación Física (QR) - Solo Administradores de Archivo -->
            @can('update', $expedient)
                <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 text-center">
                    <div class="p-4">
                        <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 mb-6 uppercase tracking-widest">Identificación Física</h3>
                        <div class="flex justify-center mb-6 bg-white p-4 rounded-3xl inline-block mx-auto border-4 border-slate-50 shadow-inner">
                            {!! QrCode::size(140)->generate($expedient->qr_content) !!}
                        </div>
                        <div class="text-xs font-black tracking-[0.2em] text-slate-400 mb-8">{{ $expedient->expedient_code }}</div>
                        <x-mary-button label="Imprimir Etiqueta" icon="o-printer" link="{{ route('expedients.print', $expedient) }}" external class="btn-primary btn-outline w-full rounded-2xl h-12" />
                    </div>
                </x-mary-card>
            @endcan

            <!-- En Posesión De -->
            @if($expedient->currentHolder)
                <x-mary-card shadow class="border-none shadow-xl shadow-primary/10 bg-primary/5">
                    <div class="p-4">
                        <h3 class="text-[10px] font-black text-primary mb-4 uppercase tracking-widest">En Posesión De</h3>
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white rounded-2xl shadow-sm">
                                <x-mary-icon name="o-user-circle" class="w-8 h-8 text-primary" />
                            </div>
                            <div>
                                <p class="font-black text-slate-800 dark:text-slate-100 leading-tight">{{ $expedient->currentHolder->name }}</p>
                                <p class="text-xs text-slate-500 font-medium">{{ $expedient->currentHolder->email }}</p>
                            </div>
                        </div>
                    </div>
                </x-mary-card>
            @endif

            <!-- Historial Reciente -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                <div class="p-4">
                    <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 mb-6 uppercase tracking-widest">Historial Reciente</h3>
                    <div class="overflow-y-auto max-h-96 pr-2">
                        <div class="space-y-6">
                            @forelse($expedient->movements->take(5) as $movement)
                                <div class="relative pl-8 border-l-2 border-slate-100 pb-1 last:pb-0">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary border-4 border-white"></div>
                                    <div class="mb-1">
                                        <span class="text-xs font-black uppercase tracking-wider text-primary">{{ $movement->movement_type->label() }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 ml-2">{{ $movement->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Por: {{ $movement->user->name ?? 'Sistema' }}</p>
                                    @if($movement->notes)
                                        <div class="mt-2 text-[11px] italic text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                            "{{ $movement->notes }}"
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-center text-slate-400 py-8">No hay movimientos registrados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>
    
    <!-- Modal para Reporte de Extravío -->
    <x-mary-modal wire:model="showLostModal" class="p-8 modal-wide">
        <div class="flex items-center gap-6 mb-8 border-b border-slate-100 pb-6">
            <div class="w-16 h-16 bg-rose-600 text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-xl shadow-rose-500/20">
                <x-mary-icon name="o-exclamation-triangle" class="w-8 h-8" />
            </div>
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter leading-none mb-1">Reportar Extravío</h3>
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Protocolo de incidencia física</p>
            </div>
        </div>

        <div class="space-y-6">
            <x-mary-alert icon="o-exclamation-triangle" class="alert-error bg-error/10 text-error border-none rounded-2xl text-xs font-bold leading-relaxed">
                Esta acción marcará el expediente como NO DISPONIBLE para préstamos hasta que sea localizado.
            </x-mary-alert>

            <x-mary-textarea label="Notas del Incidente (Opcional)" wire:model="notes" placeholder="Describa los detalles del último avistamiento o búsqueda..." rows="4" class="rounded-2xl border-slate-100 p-4" />
        </div>

        <x-slot:actions>
            <div class="flex gap-4 w-full justify-end pt-4">
                <x-mary-button label="Cancelar" wire:click="$toggle('showLostModal')" class="btn-ghost rounded-xl px-6" />
                <x-mary-button label="Confirmar Extravío" wire:click="confirmMarkAsLost" class="btn-error text-white rounded-xl px-8 font-black uppercase text-xs tracking-widest shadow-xl shadow-error/20" spinner="confirmMarkAsLost" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
