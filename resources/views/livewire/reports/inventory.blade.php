<div>
    <div class="mb-10 flex justify-between items-center no-print">
        <x-mary-header title="Inventario General" subtitle="Reporte detallado por ubicación física" />
        <div class="flex gap-4">
            <x-mary-button label="Imprimir Reporte" icon="o-printer" onclick="window.print()" class="btn-primary rounded-xl px-8" />
            <x-mary-button label="Volver" icon="o-arrow-left" link="{{ route('locations.index') }}" class="btn-ghost" />
        </div>
    </div>

    <div class="bg-white p-4 rounded-3xl print:p-0 print:shadow-none shadow-xl border border-slate-100">
        <!-- Header for Print -->
        <div class="hidden print:block border-b-2 border-slate-900 pb-2 mb-4">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-xl font-black tracking-tighter uppercase">Inventario de Archivo</h1>
                    <p class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">Listado Alfabético | Generado: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
                <div class="text-[8px] font-bold uppercase">Total: {{ $expedients->count() }} Registros</div>
            </div>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b-2 border-slate-900 print:bg-slate-100">
                    <th class="px-2 py-1 text-[8px] font-black uppercase tracking-widest w-24">RFC</th>
                    <th class="px-2 py-1 text-[8px] font-black uppercase tracking-widest w-20"># Emp</th>
                    <th class="px-2 py-1 text-[8px] font-black uppercase tracking-widest">Nombre</th>
                    <th class="px-2 py-1 text-[8px] font-black uppercase tracking-widest w-20">Gaveta</th>
                    <th class="px-2 py-1 text-[8px] font-black uppercase tracking-widest w-20">Cajón</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expedients as $exp)
                    <tr class="border-b border-slate-100 last:border-0 print:border-slate-300">
                        <td class="px-2 py-0.5 text-[8px] font-black text-slate-900">{{ $exp->employee->rfc }}</td>
                        <td class="px-2 py-0.5 text-[8px] font-bold text-slate-600">{{ $exp->employee->employee_number }}</td>
                        <td class="px-2 py-0.5 text-[8px] font-bold text-slate-800">{{ $exp->employee->last_name }}, {{ $exp->employee->first_name }}</td>
                        <td class="px-2 py-0.5 text-[8px] font-black text-slate-900">{{ $exp->currentLocation->cabinet ?? 'N/A' }}</td>
                        <td class="px-2 py-0.5 text-[8px] font-black text-slate-900">{{ $exp->currentLocation->drawer ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 8px !important; }
            .rounded-3xl { border-radius: 0 !important; }
            .p-4 { padding: 0 !important; }
            table { page-break-inside: auto; width: 100% !important; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            @page {
                margin: 0.5cm;
                size: portrait;
            }
        }
    </style>
</div>
