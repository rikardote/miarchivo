<div class="max-w-4xl mx-auto bg-white p-8 rounded-3xl shadow-xl border border-slate-200 print:shadow-none print:border-none print:p-0">
    <!-- Barra Superior de Control de Impresión (Oculta al imprimir) -->
    <div class="no-print mb-8 flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
        <div class="flex items-center gap-3">
            <x-mary-button label="Volver a Despacho" icon="o-arrow-left" link="{{ route('loans.dispatch') }}" class="btn-ghost btn-sm rounded-xl font-bold" />
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/20">
                <x-mary-icon name="o-printer" class="w-4 h-4 mr-2" />
                Imprimir Hoja de Surtido
            </button>
        </div>
    </div>

    <!-- Encabezado Institucional -->
    <div class="border-b-2 border-slate-900 pb-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('60issste.png') }}" alt="Logo ISSSTE" class="h-14 w-auto object-contain" />
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tight text-slate-900">ISSSTE BAJA CALIFORNIA</h1>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Subdelegación de Administración • Departamento de Recursos Humanos</p>
                    <p class="text-sm font-black uppercase tracking-wider text-primary mt-0.5">Hoja de Surtido y Vale de Entrega de Expedientes</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-black uppercase text-slate-400">Fecha de Emisión</div>
                <div class="text-sm font-black text-slate-900">{{ $generatedAt }}</div>
                <div class="text-xs font-bold text-slate-500 mt-1">Total por Extraer: <strong>{{ $loans->count() }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Instrucciones Rápidas -->
    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 mb-6 text-xs text-slate-600 print:bg-transparent print:border-slate-300">
        <strong>Instrucciones:</strong> El operativo de planta baja debe extraer los fólders respetando la ubicación física indicada. Al entregar a Recursos Humanos, ambas partes deben firmar el pie de página.
    </div>

    @if($loans->isEmpty())
        <div class="py-12 text-center text-slate-400">
            <p class="text-sm font-bold">No hay solicitudes pendientes de surtido en este momento.</p>
        </div>
    @else
        <!-- Tabla de Picking / Surtido Ordenada por Ubicación -->
        <table class="w-full text-left border-collapse mb-8 text-xs">
            <thead>
                <tr class="border-b-2 border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-700 bg-slate-100 print:bg-transparent">
                    <th class="py-2.5 px-2 w-8 text-center">✓</th>
                    <th class="py-2.5 px-3">Ubicación (Gaveta)</th>
                    <th class="py-2.5 px-3">Código</th>
                    <th class="py-2.5 px-3">Trabajador (RFC y Nombre)</th>
                    <th class="py-2.5 px-3">Solicitante (RH)</th>
                    <th class="py-2.5 px-3">Motivo / Obs</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($loans as $loan)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-2 text-center">
                            <div class="w-4 h-4 border-2 border-slate-400 rounded inline-block"></div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-black text-slate-900 bg-slate-100 print:bg-transparent px-2 py-1 rounded border border-slate-200 print:border-none">
                                {{ $loan->expedient?->currentLocation?->full_label ?? 'Sin ubicación' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono font-black text-slate-900">
                            {{ $loan->expedient?->expedient_code ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-900">{{ $loan->expedient?->employee?->first_name }} {{ $loan->expedient?->employee?->last_name }}</div>
                            <div class="text-[10px] font-mono text-slate-500">{{ $loan->expedient?->employee?->rfc }}</div>
                        </td>
                        <td class="py-3 px-3 font-bold text-slate-700">
                            {{ $loan->requester?->name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-3 text-[10px] text-slate-500 italic">
                            {{ $loan->observations ?: 'Sin observaciones' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Firmas de Conformidad -->
        <div class="mt-12 pt-8 border-t-2 border-slate-300 grid grid-cols-2 gap-12 text-center text-xs">
            <div>
                <div class="border-b border-slate-400 w-3/4 mx-auto mb-2 h-12"></div>
                <p class="font-black text-slate-900 uppercase">Entregó (Planta Baja)</p>
                <p class="text-[10px] text-slate-500">Operativo / Archivista en Turno</p>
            </div>
            <div>
                <div class="border-b border-slate-400 w-3/4 mx-auto mb-2 h-12"></div>
                <p class="font-black text-slate-900 uppercase">Recibió (Recursos Humanos)</p>
                <p class="text-[10px] text-slate-500">Personal Solicitante / Control</p>
            </div>
        </div>
    @endif
</div>
