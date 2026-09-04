<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Archivo') }} - Iniciar Sesión</title>

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

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('logo_claro_archivo_2026.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" href="{{ asset('logo_oscuro_archivo_2026.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" href="{{ asset('logo_claro_archivo_2026.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo_claro_archivo_2026.png') }}">
    <link rel="manifest" href="/manifest.json">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-[#041d33] min-h-screen flex flex-col selection:bg-[#073256] selection:text-white">
    
    <!-- Header flotante con fecha y toggle de tema -->
    <header class="w-full p-4 sm:p-6 lg:p-10 flex items-center justify-between sm:justify-end gap-3 absolute top-0 z-30">
        <div class="sm:hidden flex items-center">
            <img src="{{ asset('logo_horizontal_claro.png') }}" alt="Logo" class="h-7 w-auto object-contain rounded-lg dark:hidden" />
            <img src="{{ asset('logo_horizontal_oscuro.png') }}" alt="Logo" class="h-7 w-auto object-contain rounded-lg hidden dark:block" />
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden sm:block text-xs font-black text-slate-500 dark:text-slate-300 uppercase tracking-widest bg-white/70 dark:bg-[#073256]/80 px-4 py-2 rounded-full backdrop-blur-md border border-slate-200/60 dark:border-[#0c4472]/60 shadow-sm">
                ISSSTE BAJA CALIFORNIA &copy; {{ date('Y') }}
            </div>
            <div class="bg-white/80 dark:bg-[#073256]/80 p-1.5 rounded-2xl backdrop-blur-md border border-slate-200/60 dark:border-[#0c4472]/60 shadow-sm">
                <x-mary-theme-toggle darkTheme="dark" lightTheme="light" class="btn btn-ghost btn-sm btn-circle text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#0c4472]/50" />
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow flex flex-col md:flex-row min-h-screen items-stretch relative overflow-hidden">
        
        <!-- Marca de agua de fondo (Global Background Watermark) -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.02] md:opacity-[0.03] pointer-events-none select-none z-10 overflow-hidden">
            <span class="text-[20rem] lg:text-[28rem] font-black tracking-tighter text-slate-900 dark:text-white transform -rotate-12 whitespace-nowrap uppercase">ISSSTE</span>
        </div>
        
        <!-- Left Side: Hero Institucional -->
        <div class="w-full md:w-5/12 lg:w-1/2 bg-[#073256] p-8 sm:p-12 lg:p-20 flex flex-col justify-between relative overflow-hidden min-h-[380px] md:min-h-screen">
            <!-- Destellos decorativos -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-white opacity-5 mix-blend-overlay"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-[#C4A462] opacity-15 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/3 w-64 h-64 rounded-full bg-blue-500/10 blur-2xl"></div>

            <!-- Logo Superior Izquierdo -->
            <div class="relative z-20 hidden sm:block">
                <img src="{{ asset('logo_horizontal_oscuro.png') }}" alt="Logo Institucional" class="h-10 lg:h-11 w-auto object-contain rounded-xl hover:opacity-95 transition-opacity">
            </div>
            
            <div class="relative z-20 max-w-lg my-auto py-8 sm:py-12">
                <div class="w-16 h-1.5 bg-[#C4A462] mb-6 sm:mb-8 rounded-full shadow-sm shadow-[#C4A462]/30"></div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-4 tracking-tight">
                    Sistema de<br>
                    <span class="text-[#C4A462]">Gestión de Archivo</span>
                </h1>
                <p class="text-base sm:text-lg text-slate-200/90 leading-relaxed font-medium">
                    Control centralizado, trazabilidad física y digital de expedientes de personal para la Representación Estatal Baja California.
                </p>
            </div>

            <!-- Footer Institucional Izquierdo -->
            <div class="relative z-20 text-xs text-white/60 font-semibold tracking-wider uppercase hidden sm:block">
                Subdelegación de Administración • Departamento de Recursos Humanos
            </div>
        </div>

        <!-- Right Side: Formulario de Acceso -->
        <div class="w-full md:w-7/12 lg:w-1/2 bg-slate-50 dark:bg-[#041d33] p-6 sm:p-12 lg:p-20 flex flex-col justify-center items-center relative">
            
            <div class="w-full max-w-md">
                
                <div class="bg-white dark:bg-[#073256] rounded-3xl p-8 sm:p-10 shadow-2xl shadow-slate-900/5 dark:shadow-2xl dark:shadow-black/40 border border-slate-200/80 dark:border-[#0c4472]/70">
                    
                    <div class="mb-8">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Iniciar Sesión</h2>
                        <p class="text-slate-500 dark:text-slate-300 text-sm mt-1.5 font-medium">Ingresa tus credenciales para acceder a la plataforma.</p>
                    </div>

                    <!-- Estado de Sesión -->
                    @if (session('status'))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="email" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Correo Electrónico Institucional
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <x-mary-icon name="o-envelope" class="w-5 h-5" />
                                </div>
                                <input 
                                    id="email" 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autofocus 
                                    autocomplete="username"
                                    placeholder="usuario@issste.gob.mx"
                                    class="block w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-[#0c4472] bg-slate-50/50 dark:bg-[#041d33] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-400 focus:outline-none focus:border-[#C4A462] focus:ring-4 focus:ring-[#C4A462]/10 transition-all text-sm font-medium" 
                                />
                            </div>
                            @if ($errors->has('email'))
                                <p class="mt-2 text-xs font-bold text-rose-500 flex items-center gap-1">
                                    <x-mary-icon name="o-exclamation-circle" class="w-3.5 h-3.5" />
                                    {{ $errors->first('email') }}
                                </p>
                            @endif
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    Contraseña
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#073256] dark:text-[#C4A462] hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <x-mary-icon name="o-lock-closed" class="w-5 h-5" />
                                </div>
                                <input 
                                    id="password" 
                                    type="password" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="block w-full pl-11 pr-12 py-3.5 rounded-2xl border border-slate-200 dark:border-[#0c4472] bg-slate-50/50 dark:bg-[#041d33] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-400 focus:outline-none focus:border-[#C4A462] focus:ring-4 focus:ring-[#C4A462]/10 transition-all text-sm font-medium" 
                                />
                                <button 
                                    type="button" 
                                    id="btn-toggle-password"
                                    tabindex="-1"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
                                    <svg id="icon-eye-open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg id="icon-eye-closed" class="w-5 h-5" style="display: none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @if ($errors->has('password'))
                                <p class="mt-2 text-xs font-bold text-rose-500 flex items-center gap-1">
                                    <x-mary-icon name="o-exclamation-circle" class="w-3.5 h-3.5" />
                                    {{ $errors->first('password') }}
                                </p>
                            @endif
                        </div>

                        <!-- Recordar Sesión -->
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer group select-none">
                                <input 
                                    id="remember_me" 
                                    type="checkbox" 
                                    name="remember"
                                    class="w-4 h-4 rounded-lg border-slate-300 dark:border-[#0c4472] dark:bg-[#041d33] text-[#073256] dark:text-[#C4A462] focus:ring-[#C4A462] focus:ring-offset-0 cursor-pointer" 
                                />
                                <span class="ms-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
                                    Mantener sesión iniciada
                                </span>
                            </label>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="pt-3">
                            <button 
                                type="submit" 
                                class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl shadow-xl shadow-[#073256]/20 dark:shadow-none text-xs font-black uppercase tracking-widest text-white bg-[#073256] hover:bg-[#0a416e] dark:bg-[#C4A462] dark:hover:bg-[#b08f4c] dark:text-[#073256] active:scale-[0.99] focus:outline-none focus:ring-4 focus:ring-[#C4A462]/20 transition-all cursor-pointer">
                                <span>Ingresar al Sistema</span>
                                <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                            </button>
                        </div>
                    </form>

                </div>

                <div class="mt-8 text-center sm:hidden">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">ISSSTE BAJA CALIFORNIA &copy; {{ date('Y') }}</p>
                </div>

            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('btn-toggle-password');
            const passInput = document.getElementById('password');
            const eyeOpen = document.getElementById('icon-eye-open');
            const eyeClosed = document.getElementById('icon-eye-closed');

            if (toggleBtn && passInput && eyeOpen && eyeClosed) {
                toggleBtn.addEventListener('click', function () {
                    if (passInput.type === 'password') {
                        passInput.type = 'text';
                        eyeOpen.style.display = 'none';
                        eyeClosed.style.display = 'block';
                    } else {
                        passInput.type = 'password';
                        eyeOpen.style.display = 'block';
                        eyeClosed.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
