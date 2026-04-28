<div>
    <x-mary-header title="Gestionar Préstamo" subtitle="Solicitud de {{ $loan->requester->name ?? 'Usuario desconocido' }}" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('loans.index') }}">Volver</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Detalles de la Solicitud -->
        <div class="space-y-6">
            <x-mary-card shadow class="border-none shadow-xl shadow-slate-200/50">
                <div class="space-y-6 p-4">
                    <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 mb-2">Detalles del Préstamo</h3>
                    
                    <!-- Estado -->
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Estado Actual</p>
                        </div>
                        <x-mary-badge :value="optional($loan->status)->label() ?? 'Desconocido'" class="badge-{{ optional($loan->status)->color() ?? 'neutral' }} font-black px-4 py-3" />
                    </div>
                    
                    <!-- Grid de información -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-folder" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Expediente</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $loan->expedient->expedient_code ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-user" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Solicitante</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $loan->requester->name ?? 'Usuario Eliminado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-calendar" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Fecha Solicitud</p>
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ optional($loan->requested_at)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if($loan->due_date)
                        <div class="flex items-center gap-5 group">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl group-hover:bg-primary/10 transition-colors">
                                <x-mary-icon name="o-clock" class="w-6 h-6 text-slate-500 group-hover:text-primary" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Vencimiento</p>
                                <p class="text-lg font-black {{ $loan->isOverdue() ? 'text-error' : 'text-slate-800 dark:text-slate-100' }}">
                                    {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($loan->observations)
                        <div class="mt-8 pt-6 border-t border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Observaciones del solicitante</p>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-white/5">
                                <p class="text-sm italic text-slate-600 dark:text-slate-300 leading-relaxed font-medium">"{{ $loan->observations }}"</p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-mary-card>
        </div>

        <!-- Acciones -->
        <div>
            <x-mary-card title="Acciones Disponibles" class="bg-base-200/50">
                
                @if(!$loan->expedient)
                    <x-mary-alert icon="o-exclamation-triangle" title="Expediente no encontrado" class="alert-error">
                        El expediente asociado a esta solicitud ya no existe en la base de datos.
                    </x-mary-alert>
                @elseif($loan->status === \App\Enums\LoanStatus::Pending)
                    <div class="space-y-4">
                        @can('loans.approve')
                            <p class="text-sm text-gray-600">La solicitud está pendiente de revisión. Puedes aprobarla para reservar el expediente, o cancelarla.</p>
                            <div class="flex space-x-2">
                                <x-mary-button label="Aprobar" icon="o-check" class="btn-success" wire:click="triggerAction('approve')" spinner />
                                <x-mary-button label="Rechazar" icon="o-x-mark" class="btn-error" wire:click="triggerAction('cancel')" spinner />
                            </div>
                        @else
                            <x-mary-alert icon="o-clock" title="En espera" class="alert-info">
                                Tu solicitud está siendo revisada por el departamento de archivo. Te notificaremos cuando sea aprobada.
                            </x-mary-alert>
                        @endcan
                    </div>
                @elseif($loan->status === \App\Enums\LoanStatus::Approved || $loan->status === \App\Enums\LoanStatus::Reserved)
                    <div class="space-y-4">
                        @can('loans.deliver')
                            <p class="text-sm text-gray-600">El expediente está reservado. Requiere verificación con contraseña (SUDO) al momento de entregarlo físicamente.</p>
                            <x-mary-button label="Entregar Expediente" icon="o-hand-raised" class="btn-primary w-full" wire:click="triggerAction('deliver')" spinner />
                        @else
                            <x-mary-alert icon="o-check-circle" title="¡Aprobado!" class="alert-success">
                                Tu solicitud ha sido aprobada. Por favor, acude al archivo físico para recoger tu expediente.
                            </x-mary-alert>
                        @endcan
                    </div>
                @elseif($loan->status === \App\Enums\LoanStatus::Delivered)
                    <div class="space-y-4">
                        @can('loans.return')
                            <p class="text-sm text-gray-600">El expediente está actualmente en posesión del solicitante. Requiere verificación con contraseña (SUDO) para recibirlo de vuelta en el archivo.</p>
                            <div class="space-y-3">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">Notas de devolución (opcional)</label>
                                <textarea 
                                    wire:model="notes" 
                                    placeholder="Ej. Faltan hojas, carpeta dañada..." 
                                    rows="2"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl p-5 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none resize-none"
                                ></textarea>
                            </div>
                            <x-mary-button label="Registrar Devolución" icon="o-arrow-uturn-down" class="btn-accent w-full h-14 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-accent/20" wire:click="triggerAction('return')" spinner />
                        @else
                            <x-mary-alert icon="o-briefcase" title="En tu posesión" class="alert-primary">
                                Tienes este expediente en tu poder. Recuerda devolverlo a tiempo para evitar sanciones.
                            </x-mary-alert>
                        @endcan
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 flex flex-col items-center">
                        <x-mary-icon name="o-lock-closed" class="w-12 h-12 mb-2 text-base-300" />
                        <p>No hay acciones disponibles para este estado.</p>
                    </div>
                @endif

            </x-mary-card>
        </div>
    </div>

    <!-- Sudo Modal -->
    <x-mary-modal wire:model="sudoModalOpen" title="Verificación de Identidad Requerida" separator>
        <div class="py-4">
            <p class="mb-4 text-sm text-gray-600">Para continuar con esta acción crítica, por favor confirma tu identidad ingresando tu contraseña.</p>
            <div class="space-y-3">
                <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">Tu Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-primary">
                        <x-mary-icon name="o-key" class="w-5 h-5" />
                    </div>
                    <input 
                        type="password"
                        wire:model="sudoPassword" 
                        placeholder="Contraseña..." 
                        class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl h-16 pl-14 pr-6 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none"
                    />
                </div>
            </div>
        </div>
        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="$set('sudoModalOpen', false)" class="btn-ghost" />
            <x-mary-button label="Confirmar Acción" wire:click="confirmSudoAndExecute" class="btn-primary" spinner />
        </x-slot:actions>
    </x-mary-modal>
</div>
