<div>
    <x-mary-header title="Gestionar Préstamo" subtitle="Solicitud de {{ $loan->requester->name ?? 'Usuario desconocido' }}" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-left" class="btn-ghost" link="{{ route('loans.index') }}">Volver</x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Detalles de la Solicitud -->
        <div class="space-y-6">
            <x-mary-card title="Detalles del Préstamo">
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="text-gray-500">Estado</span>
                        <x-mary-badge :value="optional($loan->status)->label() ?? 'Desconocido'" class="badge-{{ optional($loan->status)->color() ?? 'neutral' }} font-bold" />
                    </div>
                    
                    <x-mary-stat title="Expediente" value="{{ $loan->expedient->expedient_code ?? 'N/A' }}" icon="o-folder" />
                    <x-mary-stat title="Solicitante" value="{{ $loan->requester->name ?? 'Usuario Eliminado' }}" icon="o-user" />
                    <x-mary-stat title="Fecha Solicitud" value="{{ optional($loan->requested_at)->format('d/m/Y H:i') ?? 'N/A' }}" icon="o-calendar" />
                    
                    @if($loan->due_date)
                        <x-mary-stat title="Vencimiento" value="{{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}" icon="o-clock" 
                            color="{{ $loan->isOverdue() ? 'text-error' : '' }}" />
                    @endif

                    @if($loan->observations)
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Observaciones del solicitante:</p>
                            <p class="text-sm italic bg-base-200 p-3 rounded mt-1">"{{ $loan->observations }}"</p>
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
                        <p class="text-sm text-gray-600">La solicitud está pendiente de revisión. Puedes aprobarla para reservar el expediente, o cancelarla.</p>
                        <div class="flex space-x-2">
                            <x-mary-button label="Aprobar" icon="o-check" class="btn-success" wire:click="triggerAction('approve')" spinner />
                            <x-mary-button label="Rechazar" icon="o-x-mark" class="btn-error" wire:click="triggerAction('cancel')" spinner />
                        </div>
                    </div>
                @elseif($loan->status === \App\Enums\LoanStatus::Approved || $loan->status === \App\Enums\LoanStatus::Reserved)
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">El expediente está reservado. Requiere verificación con contraseña (SUDO) al momento de entregarlo físicamente.</p>
                        <x-mary-button label="Entregar Expediente" icon="o-hand-raised" class="btn-primary w-full" wire:click="triggerAction('deliver')" spinner />
                    </div>
                @elseif($loan->status === \App\Enums\LoanStatus::Delivered)
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">El expediente está actualmente en posesión del solicitante. Requiere verificación con contraseña (SUDO) para recibirlo de vuelta en el archivo.</p>
                        
                        <x-mary-textarea wire:model="notes" label="Notas de devolución (opcional)" placeholder="Ej. Faltan hojas, carpeta dañada..." rows="2" />

                        <x-mary-button label="Registrar Devolución" icon="o-arrow-uturn-down" class="btn-accent w-full" wire:click="triggerAction('return')" spinner />
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
            <x-mary-input label="Tu Contraseña" wire:model="sudoPassword" type="password" icon="o-key" placeholder="Contraseña..." />
        </div>
        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="$set('sudoModalOpen', false)" class="btn-ghost" />
            <x-mary-button label="Confirmar Acción" wire:click="confirmSudoAndExecute" class="btn-primary" spinner />
        </x-slot:actions>
    </x-mary-modal>
</div>
