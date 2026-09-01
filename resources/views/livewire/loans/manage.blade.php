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
                @elseif($loan->status === \App\Enums\LoanStatus::Approved)
                    <div class="space-y-4">
                        @can('loans.deliver')
                            <div class="p-4 bg-info/10 border border-info/20 rounded-2xl space-y-2">
                                <div class="flex items-center gap-2 text-info font-black text-xs uppercase tracking-wider">
                                    <x-mary-icon name="o-clock" class="w-4 h-4" />
                                    <span>Aprobado • Esperando extracción en Planta Baja</span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    La orden ya está activa en la pantalla del archivista en Planta Baja para su extracción física del cajón. En cuanto el operador lo marque como surtido, se habilitará el botón de entrega.
                                </p>
                            </div>
                            <div class="pt-2">
                                <x-mary-button label="Forzar Entrega Inmediata (Si ya lo tienes en mano)" icon="o-hand-raised" class="btn-ghost btn-xs text-slate-400 hover:text-primary w-full text-center" wire:click="triggerAction('deliver')" spinner />
                            </div>
                        @else
                            <x-mary-alert icon="o-check-circle" title="¡Aprobado!" class="alert-success">
                                Tu solicitud fue aprobada por la Jefatura. El personal de archivo en Planta Baja está preparando tu expediente.
                            </x-mary-alert>
                        @endcan
                    </div>
                @elseif($loan->status === \App\Enums\LoanStatus::Reserved)
                    <div class="space-y-4">
                        @can('loans.deliver')
                            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl space-y-1 mb-2">
                                <div class="flex items-center gap-2 text-emerald-600 font-black text-xs uppercase tracking-wider">
                                    <x-mary-icon name="o-check-circle" class="w-4 h-4" />
                                    <span>Surtido • Fólder Físico en Mesa de Control</span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    El operador de Planta Baja ya extrajo el expediente y lo envió a tu mesa. Requiere verificación con contraseña (SUDO) al entregarlo.
                                </p>
                            </div>
                            <x-mary-button label="Entregar al Solicitante" icon="o-hand-raised" class="btn-primary w-full h-14 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-primary/20" wire:click="triggerAction('deliver')" spinner />
                        @else
                            <x-mary-alert icon="o-sparkles" title="¡Listo para Recoger!" class="alert-success">
                                Tu expediente ya fue surtido y está disponible en la mesa de control de Recursos Humanos. Puedes pasar a recogerlo.
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
                    <div class="space-y-6 py-2">
                        @can('loans.create')
                            @if($loan->expedient && $loan->expedient->current_status === \App\Enums\ExpedientStatus::Available)
                                <div class="p-5 bg-primary/5 border border-primary/20 rounded-2xl space-y-4">
                                    <div class="flex items-center gap-3 text-primary">
                                        <div class="p-2.5 bg-primary/10 rounded-xl">
                                            <x-mary-icon name="o-arrow-path" class="w-5 h-5 text-primary" />
                                        </div>
                                        <div>
                                            <h4 class="font-black text-sm text-slate-800 dark:text-slate-100">¿Necesitas este expediente de nuevo?</h4>
                                            <p class="text-xs text-slate-500">Disponible para solicitar de inmediato.</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                        El expediente <strong>{{ $loan->expedient->expedient_code }}</strong> se encuentra guardado en su gaveta. Puedes generar una nueva solicitud con un solo clic.
                                    </p>
                                    <x-mary-button 
                                        label="Solicitar Este Expediente Otra Vez" 
                                        icon="o-arrow-path" 
                                        wire:click="openRequestAgainModal" 
                                        class="btn-primary w-full h-12 rounded-xl font-black uppercase text-xs tracking-wider shadow-lg shadow-primary/20" 
                                        spinner="openRequestAgainModal"
                                    />
                                </div>
                            @elseif($loan->expedient)
                                <div class="p-5 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-white/5 space-y-2">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Estado del expediente físico:</p>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-{{ $loan->expedient->current_status->color() }}-500/10 text-{{ $loan->expedient->current_status->color() }}-600">
                                            {{ $loan->expedient->current_status->label() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400">Actualmente no está disponible en gaveta para un nuevo préstamo.</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6 text-gray-500 flex flex-col items-center">
                                <x-mary-icon name="o-lock-closed" class="w-12 h-12 mb-2 text-base-300" />
                                <p>No hay acciones disponibles para este estado.</p>
                            </div>
                        @endcan
                    </div>
                @endif

            </x-mary-card>
        </div>
    </div>

    <!-- Modal para Solicitar Nuevamente -->
    <x-mary-modal wire:model="requestAgainModalOpen" title="Solicitar Nuevamente este Expediente" separator>
        <div class="py-4 space-y-4">
            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-white/5 flex items-center gap-3">
                <x-mary-icon name="o-folder" class="w-8 h-8 text-primary" />
                <div>
                    <p class="text-sm font-black text-slate-900 dark:text-white">{{ $loan->expedient->expedient_code ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500 font-bold">{{ $loan->expedient->employee->first_name ?? '' }} {{ $loan->expedient->employee->last_name ?? '' }}</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-500 block">Motivo / Observaciones (Opcional)</label>
                <textarea 
                    wire:model="requestObservations" 
                    placeholder="Ej. Revisión de documentos para trámite de pensión..." 
                    rows="3"
                    class="w-full bg-white dark:bg-slate-950 border border-slate-100 dark:border-white/5 rounded-2xl p-4 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 shadow-sm transition-premium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 outline-none resize-none text-sm"
                ></textarea>
            </div>
        </div>
        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="$set('requestAgainModalOpen', false)" class="btn-ghost" />
            <x-mary-button label="Enviar Solicitud" icon="o-paper-airplane" wire:click="submitRequestAgain" class="btn-primary" spinner="submitRequestAgain" />
        </x-slot:actions>
    </x-mary-modal>

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
