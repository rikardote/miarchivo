<div wire:poll.3s>
    <x-mary-header title="Préstamos" subtitle="Listado de solicitudes de expedientes" class="mb-6 sm:mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-document-arrow-down" wire:click="exportActiveLoans" class="btn-ghost rounded-xl sm:rounded-2xl h-11 sm:h-14 px-3 sm:px-6 font-black uppercase text-[10px] tracking-widest border border-slate-100 dark:border-white/5 hover:bg-slate-50 transition-premium" spinner>
                <span class="hidden sm:inline">Exportar</span>
                <span class="sm:hidden">CSV</span>
            </x-mary-button>
            <x-mary-button icon="o-plus" class="btn-primary shadow-xl shadow-primary/20 rounded-xl sm:rounded-2xl h-11 sm:h-14 px-4 sm:px-8 font-black uppercase text-[10px] sm:text-xs tracking-widest border-none hover:scale-105 transition-premium" link="{{ route('loans.request') }}">
                <span class="hidden sm:inline">Nueva Solicitud</span>
                <span class="sm:hidden">Solicitar</span>
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="premium-card p-3 sm:p-6 overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10 p-1 sm:p-2">
            <div class="sm:col-span-3">
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Filtrar por estado operativo..." />
            </div>
            <div>
                <x-mary-button wire:click="$set('status', '')" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-12 sm:h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'expedient.expedient_code', 'label' => 'Expediente', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 pl-4 sm:pl-6'],
                ['key' => 'requester.name', 'label' => 'Solicitante', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden md:table-cell'],
                ['key' => 'status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4'],
                ['key' => 'requested_at', 'label' => 'Fecha Solicitud', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden sm:table-cell'],
                ['key' => 'due_date', 'label' => 'Vencimiento', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 py-4 hidden sm:table-cell'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-3 sm:pr-6']
            ]" :rows="$loans" :sort-by="$sortBy" with-pagination class="table-premium">

                @scope('cell_expedient.expedient_code', $loan)
                    <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-4">
                        <div class="w-1.5 h-6 bg-primary/20 rounded-full"></div>
                        <div class="flex flex-col">
                            <span class="font-black text-slate-900 dark:text-white tracking-tighter text-sm sm:text-base">{{ $loan->expedient->expedient_code ?? 'ELIMINADO' }}</span>
                            <span class="md:hidden text-[10px] text-slate-500 dark:text-slate-400 font-bold">{{ $loan->requester->name ?? '' }}</span>
                        </div>
                    </div>
                @endscope

                @scope('cell_requester.name', $loan)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $loan->requester->name ?? 'Usuario desconocido' }}</span>
                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1 opacity-60">ID: {{ $loan->requester->id }}</span>
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
                    <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase border {{ $statusClasses }}">
                        {{ optional($loan->status)->label() ?? 'Desconocido' }}
                    </span>
                @endscope

                @scope('cell_requested_at', $loan)
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ optional($loan->requested_at)->format('d/m/Y') ?? 'N/A' }}</span>
                        <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-tighter">{{ optional($loan->requested_at)->format('H:i') ?? '' }} hrs</span>
                    </div>
                @endscope

                @scope('cell_due_date', $loan)
                    @if($loan->due_date)
                        <div class="flex flex-col">
                            <span class="text-sm font-bold {{ $loan->isOverdue() ? 'text-rose-500 font-black' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                            </span>
                            @if($loan->isOverdue())
                                <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest mt-0.5">Retrasado</span>
                            @endif
                        </div>
                    @else
                        <span class="text-slate-300">-</span>
                    @endif
                @endscope

                @scope('cell_actions', $loan)
                    <div class="flex items-center gap-1 sm:gap-2 pr-2 sm:pr-4">
                        <x-mary-button link="{{ route('loans.manage', $loan) }}" class="btn-ghost btn-xs sm:btn-sm text-slate-500 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Gestionar">
                            <x-mary-icon name="o-eye" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>
</div>
