<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Impresión de Etiqueta - {{ config('app.name') }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm 50mm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: white;
            color: black;
            width: 80mm;
            height: 50mm;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    @vite(['resources/css/app.css'])
</head>
<body>
    {{ $slot }}
    
    <div class="no-print fixed bottom-2 right-2 flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm">Imprimir</button>
        <button onclick="window.close()" class="btn btn-ghost btn-sm">Cerrar</button>
    </div>
</body>
</html>
