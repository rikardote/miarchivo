<div>
    <x-mary-header title="Tablero de Control" subtitle="Resumen general del sistema de archivo" />

    @if($isAdmin)
        <!-- Dashboard ADMINISTRADOR -->
        @if($pendingTransfersCount > 0)
            <div class="mb-6">
                <x-mary-card class="bg-warning/10 border-warning/30 shadow-lg border-2">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-warning/20 rounded-full">
                            <x-mary-icon name="o-truck" class="w-8 h-8 text-warning" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-black text-warning">Traslados Pendientes a Almacén</h3>
                            <p class="text-sm opacity-80">Se detectaron **{{ $pendingTransfersCount }}** expedientes de personal de baja que aún figuran físicamente en Delegación. Se recomienda iniciar el traslado al archivo de concentración.</p>
                        </div>
                        <x-mary-button label="Gestionar Traslados" icon="o-arrow-right" link="{{ route('expedients.index', ['filter' => 'pending_transfer']) }}" class="btn-warning" />
                    </div>
                </x-mary-card>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <x-mary-stat title="Total Expedientes" value="{{ $totalExpedients }}" icon="o-folder" class="bg-base-100 shadow-sm" />
            <x-mary-stat title="En Préstamo" value="{{ $loanedExpedients }}" icon="o-arrow-path" class="bg-primary/5 text-primary shadow-sm" />
            <x-mary-stat title="Solicitudes Pendientes" value="{{ $pendingRequests }}" icon="o-clock" class="bg-warning/5 text-warning shadow-sm" />
            <x-mary-stat title="Vencidos" value="{{ $overdueLoansCount }}" icon="o-exclamation-triangle" class="bg-error/5 text-error shadow-sm" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <x-mary-card title="Expedientes por Sede" subtitle="Distribución física proporcional">
                    <div class="space-y-6">
                        @foreach($branchStats as $branch)
                            @php
                                $percentage = $totalEmployees > 0 ? ($branch->employees_count / $totalEmployees) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between mb-2 text-sm font-medium">
                                    <span>{{ $branch->name }}</span>
                                    <span class="text-primary font-bold">{{ $branch->employees_count }} carpetas</span>
                                </div>
                                <div class="w-full bg-base-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-primary h-2.5 rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>

                <x-mary-card title="Estado de Carpetas" subtitle="Estatus operativo actual">
                    <x-slot:actions>
                        <x-mary-button icon="o-information-circle" class="btn-ghost btn-sm text-primary" @click="$dispatch('open-glossary')" tooltip="¿Qué significan estos estados?" />
                    </x-slot:actions>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($statusStats as $stat)
                            <div class="p-3 bg-{{ $stat['color'] }}/10 rounded-xl border border-{{ $stat['color'] }}/20 text-center">
                                <div class="text-[10px] uppercase font-bold tracking-wider opacity-60 mb-1">{{ $stat['label'] }}</div>
                                <div class="text-xl font-black text-{{ $stat['color'] }}">{{ $stat['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>
            </div>

            <div class="space-y-8">
                @if($overdueLoans->count() > 0)
                    <x-mary-card title="Alertas de Vencimiento" subtitle="Préstamos fuera de tiempo" class="border-l-4 border-error">
                        <div class="space-y-4">
                            @foreach($overdueLoans as $loan)
                                <div class="p-2 bg-error/5 rounded-lg border border-error/10">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-sm">{{ $loan->expedient->expedient_code }}</span>
                                        <span class="text-[10px] text-error font-bold uppercase">{{ $loan->due_date->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Solicitado por: {{ $loan->requester->name }}</p>
                                    <div class="mt-2 text-right">
                                        <x-mary-button label="Gestionar" icon="o-pencil-square" link="{{ route('loans.manage', $loan) }}" class="btn-xs btn-error btn-outline" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-mary-card>
                @endif

                <x-mary-card title="Actividad Reciente" subtitle="Historial de movimientos">
                    <div class="space-y-4">
                        @forelse($recentActivities as $activity)
                            <div class="flex gap-3 text-sm border-b border-base-200 pb-3 last:border-0 last:pb-0">
                                <div class="mt-1">
                                    <x-mary-icon name="o-bolt" class="w-4 h-4 text-primary" />
                                </div>
                                <div>
                                    <p class="leading-tight">{{ $activity->description }}</p>
                                    <span class="text-[10px] text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 py-4 text-center">Sin actividad reciente</p>
                        @endforelse
                    </div>
                </x-mary-card>

                <x-mary-card title="Accesos Directos">
                    <div class="grid grid-cols-1 gap-2">
                        <x-mary-button label="Nuevo Expediente" icon="o-plus" link="{{ route('expedients.create') }}" class="btn-outline btn-sm w-full justify-start" />
                        <x-mary-button label="Solicitud Masiva" icon="o-rectangle-stack" link="{{ route('loans.bulk') }}" class="btn-outline btn-sm w-full justify-start" />
                        <x-mary-button label="Ver Préstamos" icon="o-clipboard-document-check" link="{{ route('loans.index') }}" class="btn-outline btn-sm w-full justify-start" />
                        <x-mary-button label="Sincronizar API" icon="o-arrow-path" class="btn-outline btn-sm w-full justify-start" />
                    </div>
                </x-mary-card>
            </div>
        </div>

    @else
        <!-- Dashboard USUARIO ESTÁNDAR -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-mary-stat title="Mis Carpetas Actuales" value="{{ $myActiveLoans }}" icon="o-briefcase" class="bg-primary/5 text-primary shadow-lg border-2 border-primary/20" />
            <x-mary-stat title="Solicitudes en Trámite" value="{{ $myPendingRequests }}" icon="o-clock" class="bg-base-100 shadow-sm" />
            <x-mary-stat title="Préstamos Vencidos" value="{{ $myOverdueLoans }}" icon="o-exclamation-circle" class="bg-error/5 text-error shadow-sm" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <x-mary-card title="¿Necesitas un expediente?" subtitle="Solicítalo aquí mismo" class="bg-gradient-to-br from-primary/10 to-transparent">
                    <div class="py-10 text-center">
                        <x-mary-icon name="o-magnifying-glass-circle" class="w-16 h-16 text-primary mx-auto mb-4 opacity-50" />
                        <h3 class="text-xl font-bold mb-2">Busca y solicita en segundos</h3>
                        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Selecciona el nombre del trabajador del que necesitas la carpeta y nosotros nos encargamos de avisar al archivo.</p>
                        <x-mary-button label="Solicitar Nuevo Préstamo" icon="o-plus" link="{{ route('loans.index') }}" class="btn-primary btn-lg" />
                    </div>
                </x-mary-card>
            </div>

            <x-mary-card title="Mi Actividad Reciente" subtitle="Tus últimos movimientos">
                <div class="space-y-4">
                    @forelse($recentActivities as $activity)
                        <div class="flex gap-3 text-sm">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            <div>
                                <p class="leading-tight">{{ $activity->description }}</p>
                                <span class="text-[10px] text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4 text-center">Aún no has realizado solicitudes</p>
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    @endif

    <!-- Glosario de Estados -->
    <x-mary-modal wire:model="showGlossary" title="Glosario de Estados" separator>
        <div class="space-y-4">
            <p class="text-sm text-gray-500 mb-4">A continuación se detalla el significado de cada estado operativo de los expedientes:</p>
            
            <div class="grid grid-cols-1 gap-3">
                <div class="flex items-center gap-3 p-2 rounded-lg border border-success/20 bg-success/5">
                    <x-mary-badge value="Disponible" class="badge-success whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">El expediente se encuentra físicamente en su ubicación asignada en el archivo.</p>
                </div>
                
                <div class="flex items-center gap-3 p-2 rounded-lg border border-warning/20 bg-warning/5">
                    <x-mary-badge value="Solicitado" class="badge-warning whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">Un usuario ha pedido el expediente. Está esperando aprobación del personal de archivo.</p>
                </div>
                
                <div class="flex items-center gap-3 p-2 rounded-lg border border-info/20 bg-info/5">
                    <x-mary-badge value="Reservado" class="badge-info whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">La solicitud fue aprobada. El expediente está separado y listo para ser recogido.</p>
                </div>
                
                <div class="flex items-center gap-3 p-2 rounded-lg border border-primary/20 bg-primary/5">
                    <x-mary-badge value="Prestado" class="badge-primary whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">El expediente ha sido entregado físicamente al usuario solicitante.</p>
                </div>
                
                <div class="flex items-center gap-3 p-2 rounded-lg border border-accent/20 bg-accent/5">
                    <x-mary-badge value="Devuelto" class="badge-accent whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">El expediente regresó al archivo, pero aún no se ha guardado en su estante definitivo.</p>
                </div>
                
                <div class="flex items-center gap-3 p-2 rounded-lg border border-secondary/20 bg-secondary/5">
                    <x-mary-badge value="En almacén" class="badge-secondary whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">El expediente se encuentra en una zona de tránsito o depósito temporal.</p>
                </div>

                <div class="flex items-center gap-3 p-2 rounded-lg border border-neutral/20 bg-neutral/5">
                    <x-mary-badge value="Archivado" class="badge-neutral whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs">El expediente ha sido enviado a un archivo de concentración o baja definitiva.</p>
                </div>
                
                <div class="flex items-center gap-3 p-2 rounded-lg border border-error/20 bg-error/5">
                    <x-mary-badge value="Extraviado" class="badge-error whitespace-nowrap flex-shrink-0" />
                    <p class="text-xs text-error font-medium">El expediente no ha sido localizado físicamente ni se tiene registro de préstamo activo.</p>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Entendido" wire:click="$set('showGlossary', false)" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
