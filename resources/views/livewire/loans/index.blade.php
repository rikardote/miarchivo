<div>
    <x-mary-header title="Préstamos" subtitle="Listado de solicitudes de expedientes" class="mb-10">
        <x-slot:actions>
            <x-mary-button icon="o-document-arrow-down" wire:click="exportActiveLoans" class="btn-ghost rounded-2xl h-14 px-6 font-black uppercase text-[10px] tracking-widest border border-slate-100 dark:border-white/5 hover:bg-slate-50 transition-premium" spinner>Exportar</x-mary-button>
            <x-mary-button icon="o-plus" class="btn-primary shadow-2xl shadow-primary/20 rounded-2xl h-14 px-8 font-black uppercase text-xs tracking-widest border-none hover:scale-105 transition-premium" link="{{ route('loans.request') }}">Nueva Solicitud</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="premium-card p-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 p-2">
            <div class="md:col-span-3">
                <x-mary-select wire:model.live="status" :options="$statuses" option-label="name" option-value="value" placeholder="Filtrar por estado operativo..." />
            </div>
            <div>
                <x-mary-button wire:click="$set('status', '')" icon="o-x-mark" class="btn-ghost w-full rounded-2xl h-14 font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-premium border border-transparent hover:border-slate-100">Limpiar</x-mary-button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200">
            <x-mary-table :headers="[
                ['key' => 'expedient.expedient_code', 'label' => 'Expediente', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4 pl-6'],
                ['key' => 'requester.name', 'label' => 'Solicitante', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'status', 'label' => 'Estado', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'requested_at', 'label' => 'Fecha Solicitud', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'due_date', 'label' => 'Vencimiento', 'class' => 'text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 dark:text-slate-400 py-4'],
                ['key' => 'actions', 'label' => '', 'class' => 'w-1 py-4 pr-6']
            ]" :rows="$loans" :sort-by="$sortBy" with-pagination class="table-premium">

                @scope('cell_expedient.expedient_code', $loan)
                    <div class="flex items-center gap-3 pl-4">
                        <div class="w-1.5 h-6 bg-primary/20 rounded-full"></div>
                        <span class="font-black text-slate-900 dark:text-white dark:text-slate-100 tracking-tighter text-base">{{ $loan->expedient->expedient_code ?? 'ELIMINADO' }}</span>
                    </div>
                @endscope

                @scope('cell_requester.name', $loan)
                    <div class="flex flex-col py-2">
                        <span class="font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-tight">{{ $loan->requester->name ?? 'Usuario desconocido' }}</span>
                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-1 opacity-60">Matrícula: {{ $loan->requester->id }}</span>
                    </div>
                @endscope

                @scope('cell_status', $loan)
                    <div class="px-4 py-1.5 rounded-xl bg-{{ optional($loan->status)->color() ?? 'slate' }}-500/10 text-{{ optional($loan->status)->color() ?? 'slate' }}-500 text-[9px] font-black uppercase text-center w-fit border border-{{ optional($loan->status)->color() ?? 'slate' }}-500/20 shadow-sm">
                        {{ optional($loan->status)->label() ?? 'Desconocido' }}
                    </div>
                @endscope

                @scope('cell_requested_at', $loan)
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200 dark:text-slate-300">{{ optional($loan->requested_at)->format('d/m/Y') ?? 'N/A' }}</span>
                        <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-tighter">{{ optional($loan->requested_at)->format('H:i') ?? '' }} hrs</span>
                    </div>
                @endscope

                @scope('cell_due_date', $loan)
                    @if($loan->due_date)
                        <div class="flex flex-col">
                            <span class="text-sm font-bold {{ $loan->isOverdue() ? 'text-rose-500 font-black' : 'text-slate-700 dark:text-slate-200 dark:text-slate-300' }}">
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
                    <div class="flex items-center gap-2 pr-4">
                        <x-mary-button link="{{ route('loans.manage', $loan) }}" class="btn-ghost btn-sm text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-premium group/btn" tooltip="Gestionar">
                            <x-mary-icon name="o-eye" class="w-4 h-4 group-hover/btn:scale-110" />
                        </x-mary-button>
                    </div>
                @endscope

            </x-mary-table>
        </div>
    </x-mary-card>
</div>
