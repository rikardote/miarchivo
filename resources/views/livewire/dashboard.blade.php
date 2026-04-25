<div>
    <x-mary-header title="Dashboard" subtitle="Resumen general del sistema de archivo" />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-mary-stat title="Total Expedientes" :value="$totalExpedients" icon="o-folder" class="bg-base-200" />
        <x-mary-stat title="En Préstamo" :value="$loanedExpedients" icon="o-arrow-path" class="bg-primary/10" color="text-primary" />
        <x-mary-stat title="Solicitudes Pendientes" :value="$pendingLoans" icon="o-clock" class="bg-warning/10" color="text-warning" />
        <x-mary-stat title="Vencidos" :value="$overdueLoansCount" icon="o-exclamation-triangle" class="{{ $overdueLoansCount > 0 ? 'bg-error/10 border-error/50' : 'bg-base-200' }}" color="{{ $overdueLoansCount > 0 ? 'text-error' : '' }}" />
    </div>

    @if($overdueLoansCount > 0)
        <x-mary-alert icon="o-exclamation-circle" title="Atención: Hay préstamos vencidos" class="alert-error mb-8 shadow-lg">
            Existen un total de {{ $overdueLoansCount }} expedientes que han superado su fecha de devolución.
            <x-slot:actions>
                <x-mary-button label="Ver Préstamos" link="{{ route('loans.index') }}" class="btn-sm btn-ghost bg-white/20" />
            </x-slot:actions>
        </x-mary-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <x-mary-card title="Expedientes por Sede" subtitle="Distribución física">
                    <div class="space-y-4">
                        @foreach($branchStats as $branch)
                            <div>
                                <div class="flex justify-between mb-1 text-sm">
                                    <span>{{ $branch->name }}</span>
                                    <span class="font-bold">{{ $branch->employees_count }}</span>
                                </div>
                                <progress class="progress progress-primary w-full" value="{{ $branch->employees_count }}" max="{{ $totalEmployees > 0 ? $totalEmployees : 1 }}"></progress>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>

                <x-mary-card title="Estado de Carpetas" subtitle="Disponibilidad actual">
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($statusStats as $stat)
                            <div class="p-4 bg-{{ $stat['color'] }}/10 rounded-xl border border-{{ $stat['color'] }}/20">
                                <div class="text-xs uppercase opacity-70">{{ $stat['label'] }}</div>
                                <div class="text-2xl font-bold">{{ $stat['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>
            </div>

            <x-mary-card title="Accesos Directos" subtitle="Acciones rápidas">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @can('expedients.create')
                        <x-mary-button label="Nuevo Expediente" icon="o-plus" link="{{ route('expedients.create') }}" class="btn-outline h-24" />
                    @endcan
                    <x-mary-button label="Solicitar Préstamo" icon="o-document-plus" link="{{ route('loans.request') }}" class="btn-outline h-24" />
                    @can('loans.approve')
                        <x-mary-button label="Gestionar Préstamos" icon="o-briefcase" link="{{ route('loans.index') }}" class="btn-outline h-24" />
                    @endcan
                </div>
            </x-mary-card>

            @if($overdueLoans->isNotEmpty())
                <x-mary-card title="Préstamos Vencidos (Recientes)" icon="o-clock" separator>
                    <div class="overflow-x-auto">
                        <table class="table table-compact w-full">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Responsable</th>
                                    <th>Venció</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueLoans as $loan)
                                    <tr>
                                        <td class="font-bold">{{ $loan->expedient->expedient_code }}</td>
                                        <td>{{ $loan->requester->name }}</td>
                                        <td class="text-error font-medium">{{ \Carbon\Carbon::parse($loan->due_date)->diffForHumans() }}</td>
                                        <td><x-mary-button icon="o-phone" class="btn-xs btn-ghost" tooltip="Contactar" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-mary-card>
            @endif
        </div>

        <div>
            <x-mary-card title="Actividad Reciente" subtitle="Últimas acciones del sistema">
                <div class="space-y-4">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3 text-sm">
                            <div class="mt-1">
                                <x-mary-icon name="o-bolt" class="w-4 h-4 text-gray-400" />
                            </div>
                            <div>
                                <p class="font-medium">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No hay actividad reciente.</p>
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    </div>
</div>
