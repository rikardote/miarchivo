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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 min-h-screen flex flex-col selection:bg-[#13322B] selection:text-white">
    
    <!-- Header flotante con fecha y toggle de tema -->
    <header class="w-full p-4 sm:p-6 lg:p-10 flex items-center justify-between sm:justify-end gap-3 absolute top-0 z-30">
        <div class="sm:hidden">
            <img src="{{ asset('60issste.png') }}" alt="Logo" class="h-9 w-auto object-contain" />
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden sm:block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-white/70 dark:bg-slate-900/70 px-4 py-2 rounded-full backdrop-blur-md border border-slate-200/60 dark:border-white/5 shadow-sm">
                ISSSTE BAJA CALIFORNIA &copy; {{ date('Y') }}
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 p-1.5 rounded-2xl backdrop-blur-md border border-slate-200/60 dark:border-white/5 shadow-sm">
                <x-mary-theme-toggle darkTheme="dark" lightTheme="light" class="btn btn-ghost btn-sm btn-circle text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" />
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
        <div class="w-full md:w-5/12 lg:w-1/2 bg-[#13322B] p-8 sm:p-12 lg:p-20 flex flex-col justify-between relative overflow-hidden min-h-[380px] md:min-h-screen">
            <!-- Destellos decorativos -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-white opacity-5 mix-blend-overlay"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-[#C4A462] opacity-15 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/3 w-64 h-64 rounded-full bg-emerald-500/10 blur-2xl"></div>

            <!-- Logo Superior Izquierdo -->
            <div class="relative z-20 hidden sm:block">
                <img src="{{ asset('60issste.png') }}" alt="Logo Institucional" class="h-14 lg:h-20 w-auto object-contain drop-shadow-xl brightness-0 invert opacity-95 hover:opacity-100 transition-opacity">
            </div>
            
            <div class="relative z-20 max-w-lg my-auto py-8 sm:py-12">
                <div class="w-16 h-1.5 bg-[#C4A462] mb-6 sm:mb-8 rounded-full shadow-sm shadow-[#C4A462]/30"></div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-4 tracking-tight">
                    Sistema de<br>
                    <span class="text-[#C4A462]">Gestión de Archivo</span>
                </h1>
                <p class="text-base sm:text-lg text-emerald-100/90 leading-relaxed font-medium">
                    Control centralizado, trazabilidad física y digital de expedientes de personal para la Representación Estatal Baja California.
                </p>
            </div>

            <!-- Footer Institucional Izquierdo -->
            <div class="relative z-20 text-xs text-white/60 font-semibold tracking-wider uppercase hidden sm:block">
                Subdelegación de Administración • Departamento de Recursos Humanos
            </div>
        </div>

        <!-- Right Side: Formulario de Acceso -->
        <div class="w-full md:w-7/12 lg:w-1/2 bg-slate-50 dark:bg-slate-950 p-6 sm:p-12 lg:p-20 flex flex-col justify-center items-center relative">
            
            <div class="w-full max-w-md">
                
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-10 shadow-2xl shadow-slate-900/5 dark:shadow-none border border-slate-200/80 dark:border-white/5">
                    
                    <div class="mb-8">
                        <div class="inline-block p-3 rounded-2xl bg-[#13322B]/10 dark:bg-[#C4A462]/10 text-[#13322B] dark:text-[#C4A462] mb-4">
                            <x-mary-icon name="o-lock-closed" class="w-6 h-6" />
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Iniciar Sesión</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1.5 font-medium">Ingresa tus credenciales para acceder a la plataforma.</p>
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
                                    class="block w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-[#C4A462] focus:ring-4 focus:ring-[#C4A462]/10 transition-all text-sm font-medium" 
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
                                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#13322B] dark:text-[#C4A462] hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>
                            <div class="relative" x-data="{ show: false }">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <x-mary-icon name="o-lock-closed" class="w-5 h-5" />
                                </div>
                                <input 
                                    id="password" 
                                    :type="show ? 'text' : 'password'" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="block w-full pl-11 pr-12 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-[#C4A462] focus:ring-4 focus:ring-[#C4A462]/10 transition-all text-sm font-medium" 
                                />
                                <button 
                                    type="button" 
                                    @click="show = !show" 
                                    tabindex="-1"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
                                    <template x-if="!show">
                                        <x-mary-icon name="o-eye" class="w-5 h-5" />
                                    </template>
                                    <template x-if="show">
                                        <x-mary-icon name="o-eye-slash" class="w-5 h-5" />
                                    </template>
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
                                    class="w-4 h-4 rounded-lg border-slate-300 dark:border-slate-700 text-[#13322B] focus:ring-[#C4A462] focus:ring-offset-0 cursor-pointer" 
                                />
                                <span class="ms-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
                                    Mantener sesión iniciada
                                </span>
                            </label>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="pt-3">
                            <button 
                                type="submit" 
                                class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl shadow-xl shadow-[#13322B]/20 dark:shadow-none text-xs font-black uppercase tracking-widest text-white bg-[#13322B] hover:bg-[#0c221d] active:scale-[0.99] focus:outline-none focus:ring-4 focus:ring-[#13322B]/20 transition-all cursor-pointer">
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

</body>
</html>
