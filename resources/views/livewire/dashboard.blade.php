<div>
    <x-mary-header title="Dashboard" subtitle="Resumen general del sistema de archivo" />

    @if($isAdmin)
        <!-- Dashboard ADMINISTRADOR -->
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
</div>
