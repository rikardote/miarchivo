<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Archivo') }}</title>

        <!-- Immediate Theme Initialization (Strict Light Default) -->
        <script>
            (function() {
                try {
                    if (!localStorage.getItem('mary-theme')) {
                        localStorage.setItem('mary-theme', '"light"');
                        localStorage.setItem('mary-class', '"light"');
                    }
                    const theme = localStorage.getItem('mary-theme')?.replaceAll('"', '') || 'light';
                    const themeClass = localStorage.getItem('mary-class')?.replaceAll('"', '') || theme;
                    
                    document.documentElement.setAttribute('data-theme', theme);
                    document.documentElement.setAttribute('class', themeClass);
                    if (theme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
        <div class="min-h-screen flex flex-col md:flex-row">
            <!-- Left Side: Visual/Branding -->
            <div class="hidden md:flex md:w-1/2 bg-primary items-center justify-center p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.15)_0%,transparent_50%)]"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-black/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 text-white max-w-lg">
                    <div class="mb-8">
                        <img src="{{ asset('60issste.png') }}" alt="Logo" class="h-24 w-auto brightness-0 invert" />
                    </div>
                    <h2 class="text-5xl font-black tracking-tight leading-tight mb-6">Sistema Integral de Gestión de Archivo</h2>
                    <p class="text-xl text-white/80 font-medium leading-relaxed">Control eficiente, seguro y digitalizado para el resguardo de expedientes institucionales.</p>
                    
                    <div class="mt-12 flex items-center gap-4">
                        <div class="flex -space-x-3">
                            @for($i = 1; $i <= 4; $i++)
                                <div class="w-10 h-10 rounded-full border-2 border-primary bg-slate-200"></div>
                            @endfor
                        </div>
                        <span class="text-sm font-bold text-white/70 tracking-wide uppercase italic">+2,500 Expedientes Resguardados</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="w-full md:w-1/2 flex items-center justify-center p-8 bg-white md:bg-slate-50">
                <div class="w-full max-w-md">
                    <div class="md:hidden mb-10 flex justify-center">
                        <img src="{{ asset('60issste.png') }}" alt="Logo" class="h-16 w-auto" />
                    </div>
                    
                    <div class="bg-white md:shadow-2xl md:shadow-slate-200/50 rounded-[2.5rem] p-8 md:p-12 border border-transparent md:border-slate-100">
                        {{ $slot }}
                    </div>
                    
                    <div class="mt-8 text-center">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">2026 ISSSTE BAJA CALIFORNIA</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
