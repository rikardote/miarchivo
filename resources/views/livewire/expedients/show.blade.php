<div>
    <x-mary-header title="Expediente: {{ $expedient->expedient_code }}" subtitle="Detalles y movimientos físicos" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('expedients.index') }}">Volver</x-mary-button>
            @if($expedient->isAvailable())
                <x-mary-button icon="o-document-text" class="btn-secondary" link="{{ route('loans.request', ['expedient' => $expedient->id]) }}">Solicitar</x-mary-button>
            @else
                <x-mary-button icon="o-document-text" class="btn-secondary" disabled label="No Disponible" />
            @endif
            @can('update', $expedient)
                @if($expedient->current_status->value !== 'lost')
                    <x-mary-button icon="o-exclamation-triangle" class="btn-error btn-outline" label="Extraviado" wire:click="markAsLost" />
                @else
                    <x-mary-button icon="o-check-circle" class="btn-success btn-outline" label="Recuperado" wire:click="markAsFound" />
                @endif
                <x-mary-button icon="o-pencil" class="btn-primary" link="{{ route('expedients.edit', $expedient) }}">Editar</x-mary-button>
            @endcan
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Info Principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Información del Empleado -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-4 space-y-6">
                    <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 mb-6">Información del Empleado</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-user" class="w-7 h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Nombre Completo</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $expedient->employee->first_name }} {{ $expedient->employee->last_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-identification" class="w-7 h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">RFC</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $expedient->employee->rfc }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-building-storefront" class="w-7 h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Sucursal</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $expedient->employee->branch->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            <!-- Detalles del Archivo -->
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-4 space-y-6">
                    <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 mb-6">Detalles del Archivo</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-qr-code" class="w-7 h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Código</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $expedient->expedient_code }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-book-open" class="w-7 h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Tomo</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $expedient->volume_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group md:col-span-2">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-map-pin" class="w-7 h-7 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Ubicación Física</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                    {{ $expedient->currentLocation->full_label ?? 'Sin asignar' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group pt-4 border-t border-slate-100 md:col-span-2">
                            <div class="p-3 bg-{{ $expedient->current_status->color() }}/10 rounded-2xl">
                                <x-mary-icon name="o-tag" class="w-7 h-7 text-{{ $expedient->current_status->color() }}" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Estado</p>
                                <p class="text-xl font-black text-{{ $expedient->current_status->color() }}">
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
            <!-- Identificación Física (QR) -->
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
                <x-mary-button label="Confirmar Extravío" wire:click="markAsLost" class="btn-error text-white rounded-xl px-8 font-black uppercase text-xs tracking-widest shadow-xl shadow-error/20" spinner="markAsLost" />
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
