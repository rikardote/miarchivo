<div wire:poll.3s>
    <x-mary-header title="Préstamos y Custodia" subtitle="Control de solicitudes, entregas físicas y cartera de préstamos" class="mb-6 sm:mb-8">
        <x-slot:actions>
            <x-mary-button icon="o-document-arrow-down" wire:click="exportActiveLoans" class="btn-ghost rounded-xl sm:rounded-2xl h-11 sm:h-14 px-3 sm:px-6 font-black uppercase text-[10px] tracking-widest border border-slate-100 dark:border-white/5 hover:bg-slate-50 transition-premium" spinner>
                <span class="hidden sm:inline">Exportar Listado</span>
                <span class="sm:hidden">CSV</span>
            </x-mary-button>
            <x-mary-button icon="o-plus" class="btn-primary shadow-xl shadow-primary/20 rounded-xl sm:rounded-2xl h-11 sm:h-14 px-4 sm:px-8 font-black uppercase text-[10px] sm:text-xs tracking-widest border-none hover:scale-105 transition-premium" link="{{ route('loans.request') }}">
                <span class="hidden sm:inline">Nueva Solicitud</span>
                <span class="sm:hidden">Solicitar</span>
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <!-- Pestañas Operativas (Tabs de Navegación Rápida) -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <button wire:click="setTab('all')" class="px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 {{ $tab === 'all' ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 border border-slate-200/60 dark:border-slate-800' }}">
            <x-mary-icon name="o-queue-list" class="w-4 h-4" />
            <span>Historial Completo</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ $counts['all'] }}</span>
        </button>

        <button wire:click="setTab('delivered')" class="px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 {{ $tab === 'delivered' ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 border border-slate-200/60 dark:border-slate-800' }}">
            <x-mary-icon name="o-shield-check" class="w-4 h-4" />
            <span>Prestados / Por Devolver</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'delivered' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ $counts['delivered'] }}</span>
        </button>

        <button wire:click="setTab('overdue')" class="px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 {{ $tab === 'overdue' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 hover:bg-rose-50 border border-rose-200 dark:border-rose-900/40' }}">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4 text-rose-500 {{ $tab === 'overdue' ? 'text-white' : '' }}" />
            <span>⚠️ Préstamos Vencidos</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'overdue' ? 'bg-white text-rose-600' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }}">{{ $counts['overdue'] }}</span>
        </button>

        <button wire:click="setTab('pending')" class="px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 {{ $tab === 'pending' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 border border-slate-200/60 dark:border-slate-800' }}">
            <x-mary-icon name="o-clock" class="w-4 h-4" />
            <span>Solicitudes Pendientes</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'pending' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ $counts['pending'] }}</span>
        </button>
    </div>

    <!-- Alerta Banner si hay préstamos vencidos en pestaña de vencidos -->
    @if($tab === 'overdue' && $counts['overdue'] > 0)
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-600/20 font-black">
                    <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="text-sm font-black text-rose-900 dark:text-rose-200 uppercase tracking-tight">Cartera Vencida Detectada</h4>
                    <p class="text-xs text-rose-700 dark:text-rose-300 font-medium">Hay {{ $counts['overdue'] }} expediente(s) con fecha pactada vencida fuera de estantería. Se recomienda requerir su reingreso.</p>
                </div>
            </div>
            <x-mary-button label="Exportar Requerimiento" icon="o-document-arrow-down" wire:click="exportActiveLoans" class="btn-error btn-sm text-white font-black uppercase text-[10px] tracking-wider shrink-0" spinner />
        </div>
    @endif

    <x-mary-card class="premium-card p-3 sm:p-6 overflow-hidden">
        <!-- Barra de Filtros Multifuncional -->
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 mb-6 sm:mb-8 p-1 sm:p-2">
            <!-- Búsqueda rápida -->
            <div class="sm:col-span-4">
                <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" placeholder="Buscar por código, empleado o RFC..." class="rounded-2xl border-slate-200" />
            </div>

            <!-- Filtro por Custodio Activo -->
            <div class="sm:col-span-4">
                <select wire:model.live="selectedUserId" class="select select-bordered w-full rounded-2xl text-xs font-bold border-slate-200 bg-white dark:bg-slate-900">
                    <option value="">Todos los solicitantes / custodios...</option>
                    @foreach($custodians as $custodian)
                        <option value="{{ $custodian->id }}">
                            {{ $custodian->name }} ({{ $custodian->active_loans_count }} en mano)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Estatus -->
            <div class="sm:col-span-3">
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Filtrar por estatus..." class="rounded-2xl border-slate-200" />
            </div>

            <!-- Botón Limpiar -->
            <div class="sm:col-span-1">
                <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-12 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-slate-200/60 dark:border-slate-800" tooltip="Limpiar Filtros" />
            </div>
        </div>

        <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <x-mary-table :headers="[
                ['key' => 'expedient.expedient_code', 'label' => 'Expediente', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 pl-4 sm:pl-6'],
                ['key' => 'requester.name', 'label' => 'Solicitante / Custodio', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden md:table-cell'],
                ['key' => 'status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
                ['key' => 'requested_at', 'label' => 'Fecha Solicitud', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden sm:table-cell'],
                ['key' => 'due_date', 'label' => 'Vencimiento y Mora', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden sm:table-cell'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-3 sm:pr-6']
            ]" :rows="$loans" :sort-by="$sortBy" with-pagination class="table-premium">

                @scope('cell_expedient.expedient_code', $loan)
                    <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-4 py-1">
                        <div class="w-1.5 h-8 bg-primary/30 rounded-full"></div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-black text-slate-900 dark:text-white tracking-tight text-sm sm:text-base truncate">
                                {{ $loan->expedient->expedient_code ?? 'ELIMINADO' }}
                            </span>
                            @if($loan->expedient?->employee)
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase truncate">
                                    {{ $loan->expedient->employee->last_name }}, {{ $loan->expedient->employee->first_name }}
                                </span>
                            @endif
                            <span class="md:hidden text-[10px] text-slate-400 font-bold mt-0.5">
                                Solicitó: {{ $loan->requester->name ?? '' }}
                            </span>
                        </div>
                    </div>
                @endscope

                @scope('cell_requester.name', $loan)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $loan->requester->name ?? 'Usuario desconocido' }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $loan->requester->email ?? '' }}</span>
                    </div>
                @endscope

                @scope('cell_status', $loan)
                    @php
                        $statusClasses = match($loan->status) {
                            \App\Enums\LoanStatus::Pending   => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                            \App\Enums\LoanStatus::Approved  => 'bg-sky-500/10 text-sky-600 border-sky-500/20',
                            \App\Enums\LoanStatus::Reserved  => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                            \App\Enums\LoanStatus::Delivered => 'bg-primary/10 text-primary border-primary/20',
                            \App\Enums\LoanStatus::Returned  => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                            \App\Enums\LoanStatus::Rejected  => 'bg-rose-500/10 text-rose-600 border-rose-500/20',
                            \App\Enums\LoanStatus::Cancelled => 'bg-neutral/10 text-neutral border-neutral/20',
                            default                          => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                        };
                    @endphp
                    <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase border {{ $statusClasses }} shadow-sm">
                        {{ optional($loan->status)->label() ?? 'Desconocido' }}
                    </span>
                @endscope

                @scope('cell_requested_at', $loan)
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ optional($loan->requested_at)->format('d/m/Y') ?? 'N/A' }}</span>
                        <span class="text-[10px] font-bold text-slate-400 tracking-tighter">{{ optional($loan->requested_at)->format('H:i') ?? '' }} hrs</span>
                    </div>
                @endscope

                @scope('cell_due_date', $loan)
                    @php
                        $isDelivered = $loan->status === \App\Enums\LoanStatus::Delivered;
                        $isOverdue = $loan->due_date && $loan->due_date->isPast() && $isDelivered;
                        $daysDiff = $loan->due_date ? abs((int) now()->diffInDays($loan->due_date, false)) : null;
                    @endphp
                    @if($loan->due_date)
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-black {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                            </span>
                            @if($isOverdue)
                                <span class="badge badge-error badge-sm text-[9px] font-black uppercase tracking-wider">
                                    ¡{{ $daysDiff }} día(s) de atraso!
                                </span>
                            @elseif($isDelivered && $daysDiff !== null)
                                <span class="badge badge-success badge-sm font-bold uppercase text-[9px]">
                                    Restan {{ $daysDiff }} día(s)
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="text-slate-300 text-xs">-</span>
                    @endif
                @endscope

                @scope('cell_actions', $loan)
                    <div class="flex items-center gap-1 sm:gap-2 pr-2 sm:pr-4">
                        <x-mary-button link="{{ route('loans.manage', $loan) }}" class="btn-ghost btn-xs sm:btn-sm text-slate-500 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Gestionar Préstamo">
                            <x-mary-icon name="o-eye" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>
</div>
