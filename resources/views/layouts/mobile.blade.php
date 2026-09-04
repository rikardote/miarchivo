<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Settings -->
    <title>Escáner Móvil — MiArchivo</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F1E36">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MiArchivo">
    <link rel="apple-touch-icon" href="{{ asset('logo_oscuro_archivo_2026.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo_oscuro_archivo_2026.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>

    <style>
        /* Mobile-safe viewport areas for notches & home bars */
        .safe-pb {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }
        .safe-pt {
            padding-top: env(safe-area-inset-top, 0.75rem);
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 selection:bg-[#C4A462] selection:text-[#0F1E36] overflow-x-hidden">
    
    {{ $slot }}

    <x-mary-toast />

    <!-- Audio & PWA Service Worker Scripts -->
    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('PWA ServiceWorker listo:', reg.scope))
                    .catch((err) => console.warn('PWA ServiceWorker no disponible:', err));
            });
        }

        // Web Audio Synthesizer (Instant local feedback without external MP3 files)
        window.playAudioTone = function(type) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                
                if (type === 'success') {
                    // Double pleasant high chime (880Hz -> 1320Hz)
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.12);
                    gain.gain.setValueAtTime(0.25, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.22);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.22);
                } else {
                    // Low warning buzzer (220Hz -> 180Hz)
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, ctx.currentTime);
                    osc.frequency.setValueAtTime(180, ctx.currentTime + 0.12);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.25);
                }
            } catch (e) {
                console.warn('Audio synthesizer error:', e);
            }
        };
    </script>
    @stack('scripts')
</body>
</html>
