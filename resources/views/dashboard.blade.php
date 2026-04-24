<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-mary-stat title="Total Expedientes" value="1,240" icon="o-folder" class="shadow" />
        <x-mary-stat title="Expedientes Prestados" value="45" icon="o-document-arrow-up" color="text-warning" class="shadow" />
        <x-mary-stat title="Préstamos Vencidos" value="3" icon="o-exclamation-triangle" color="text-error" class="shadow" />
        <x-mary-stat title="Total Empleados" value="1,420" icon="o-users" class="shadow" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-mary-card title="Préstamos Recientes" class="shadow">
            <p class="text-base-content/70">Aquí se mostrarán las últimas solicitudes de préstamo.</p>
        </x-mary-card>
        
        <x-mary-card title="Movimientos Recientes" class="shadow">
            <p class="text-base-content/70">Aquí se mostrarán los últimos movimientos de expedientes.</p>
        </x-mary-card>
    </div>
</x-app-layout>
