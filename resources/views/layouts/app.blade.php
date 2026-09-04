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

    <script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
    <style>
        /* Desactivar el botón de cierre (X) por defecto de los modales */
        .modal-box > label.btn-circle {
            display: none !important;
        }
        
        /* Ajuste para modales anchos */
        .modal-wide .modal-box {
            max-width: 680px !important;
            width: 95% !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        @media (max-width: 640px) {
            .modal-wide .modal-box {
                width: calc(100vw - 1.25rem) !important;
                max-width: calc(100vw - 1.25rem) !important;
                max-height: 94vh !important;
                padding: 1rem !important;
                margin: 0 auto !important;
                border-radius: 1.25rem !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100">
    <x-mary-nav sticky class="glass border-b border-slate-200 dark:border-white/10 z-[60] px-3 sm:px-6 !h-16 sm:!h-20">
        <x-slot:brand>
            <div class="flex items-center gap-2 sm:gap-3 group cursor-pointer shrink-0">
                <img src="{{ asset('60issste.png') }}" alt="Logo" class="h-8 sm:h-12 w-auto object-contain" />
                <div class="hidden sm:flex flex-col">
                    <div class="font-black text-xl sm:text-2xl tracking-tighter text-slate-900 dark:text-white leading-none">
                        Archivo<span class="text-[#C4A462]">.</span>
                    </div>
                    <div class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400 mt-1">ISSSTE BAJA CALIFORNIA</div>
                </div>
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <div class="flex items-center gap-1 sm:gap-2 shrink-0 flex-nowrap">
                @canany(['loans.deliver', 'loans.return', 'expedients.change-location', 'loans.approve'])
                    <button type="button" onclick="window.Livewire && Livewire.dispatch('open-global-scanner')" class="btn btn-ghost btn-circle btn-sm sm:btn-md !h-9 !w-9 sm:!h-10 sm:!w-10 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-primary cursor-pointer transition-colors" title="Escáner Rápido (Ctrl+K)">
                        <x-mary-icon name="o-qr-code" class="w-5 h-5" />
                    </button>
                @endcanany

                <livewire:notifications-bell />

                <label for="main-drawer" class="lg:hidden btn btn-ghost btn-circle btn-sm sm:btn-md text-slate-600 dark:text-slate-300 dark:text-white">
                    <x-mary-icon name="o-bars-3" class="w-5 h-5 sm:w-6 sm:h-6" />
                </label>
            </div>
        </x-slot:actions>
    </x-mary-nav>

    <x-mary-main with-nav full-width>
        <!-- Sidebar -->
        <x-slot:sidebar drawer="main-drawer" class="sidebar-glass z-50">

            <!-- Mobile Drawer Header with Close Button -->
            <div class="lg:hidden flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center font-black text-base shadow-sm">
                        <x-mary-icon name="o-archive-box" class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="font-black text-sm text-slate-900 dark:text-white leading-none">MiArchivo</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Navegación</div>
                    </div>
                </div>
                <label for="main-drawer" class="btn btn-ghost btn-circle btn-sm text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer" aria-label="Cerrar menú">
                    <x-mary-icon name="o-x-mark" class="w-5 h-5" />
                </label>
            </div>

            <x-mary-menu active-class="sidebar-item-active" class="px-6 space-y-3 pb-16">
                <x-mary-menu-item title="Dashboard" icon="o-chart-pie" link="{{ route('dashboard') }}" active="{{ request()->routeIs('dashboard') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 py-4 transition-premium font-black text-sm" />


                @canany(['expedients.view', 'expedients.create', 'expedients.change-location'])
                <x-mary-menu-sub title="Expedientes" icon="o-folder" open="{{ request()->routeIs('expedients.*') || request()->routeIs('reports.inventory') }}" class="text-slate-600 dark:text-white/70 font-black text-sm">
                    <x-mary-menu-item title="Buscador Central" icon="o-magnifying-glass" link="{{ route('expedients.index') }}" active="{{ request()->routeIs('expedients.index') }}" class="text-xs font-bold py-3" />
                    @can('expedients.change-location')
                        <x-mary-menu-item title="Escáner Inteligente" icon="o-qr-code" link="{{ route('expedients.scanner') }}" active="{{ request()->routeIs('expedients.scanner') }}" class="text-xs font-bold py-3" />
                        <x-mary-menu-item title="Escáner Móvil (PWA)" icon="o-device-phone-mobile" link="{{ route('mobile.scanner') }}" active="{{ request()->routeIs('mobile.scanner') }}" class="text-xs font-bold py-3 text-[#C4A462]" />
                        <x-mary-menu-item title="Auditoría de Control" icon="o-check-badge" link="{{ route('expedients.audit') }}" active="{{ request()->routeIs('expedients.audit') }}" class="text-xs font-bold py-3" />
                    @endcan
                    @can('locations.view')
                        <x-mary-menu-item title="Inventario General" icon="o-chart-bar-square" link="{{ route('reports.inventory') }}" active="{{ request()->routeIs('reports.inventory') }}" class="text-xs font-bold py-3" />
                    @endcan
                    @can('expedients.create')
                        <x-mary-menu-item title="Alta de Expediente" icon="o-plus" link="{{ route('expedients.create') }}" active="{{ request()->routeIs('expedients.create') }}" class="text-xs font-bold py-3" />
                        <x-mary-menu-item title="Captura Rápida" icon="o-bolt" link="{{ route('expedients.continuous-create') }}" active="{{ request()->routeIs('expedients.continuous-create') }}" class="text-xs font-bold py-3" />
                    @endcan
                </x-mary-menu-sub>
                @endcanany

                @canany(['loans.approve', 'loans.deliver', 'loans.return'])
                    <x-mary-menu-sub title="Préstamos" icon="o-document-text" open="{{ request()->routeIs('loans.*') }}" class="text-slate-600 dark:text-white/70 font-black text-sm">
                        @can('loans.create')
                            <x-mary-menu-item title="Bandeja Personal" icon="o-inbox" link="{{ route('loans.index', ['mine' => 1]) }}" active="{{ request()->fullUrlIs(route('loans.index', ['mine' => 1])) }}" class="text-xs font-bold py-3" />
                        @endcan
                        @canany(['loans.deliver', 'loans.return'])
                            <x-mary-menu-item title="Despacho (Planta Baja)" icon="o-truck" link="{{ route('loans.dispatch') }}" active="{{ request()->routeIs('loans.dispatch') }}" class="text-xs font-bold py-3" />
                        @endcanany
                        @can('loans.approve')
                            <x-mary-menu-item title="Carga Masiva" icon="o-rectangle-stack" link="{{ route('loans.bulk') }}" active="{{ request()->routeIs('loans.bulk') }}" class="text-xs font-bold py-3" />
                            <x-mary-menu-item title="Mesa de Control" icon="o-clipboard-document-check" link="{{ route('loans.index') }}" active="{{ request()->routeIs('loans.index') && !request()->has('mine') }}" class="text-xs font-bold py-3" />
                        @endcan
                    </x-mary-menu-sub>
                @else
                    @can('loans.create')
                        <x-mary-menu-item title="Préstamos" icon="o-document-text" link="{{ route('loans.index', ['mine' => 1]) }}" active="{{ request()->routeIs('loans.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 py-4 transition-premium font-black text-sm" />
                    @endcan
                @endcanany

                @canany(['employees.view', 'locations.view', 'users.view'])
                    <div class="px-6 py-4 mt-6 mb-2 text-[10px] font-black text-slate-300 dark:text-white/10 uppercase tracking-[0.4em] border-t border-slate-100 dark:border-white/5 pt-6">Sistema</div>
                    
                    @can('employees.view')
                        <x-mary-menu-item title="Plantilla" icon="o-identification" link="{{ route('employees.index') }}" active="{{ request()->routeIs('employees.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 transition-premium font-black text-sm" />
                    @endcan

                    @can('locations.view')
                        <x-mary-menu-item title="Archiveros" icon="o-archive-box" link="{{ route('locations.index') }}" active="{{ request()->routeIs('locations.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 transition-premium font-black text-sm" />
                    @endcan

                    @can('users.view')
                        <x-mary-menu-item title="Usuarios" icon="o-users" link="{{ route('users.index') }}" active="{{ request()->routeIs('users.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 transition-premium font-black text-sm" />
                    @endcan
                @endcanany

                <div x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false" class="relative pt-6 mt-4 border-t border-slate-100 dark:border-white/10">
                    <!-- Dropdown Content (Upward Popup) -->
                    <div 
                        x-show="userMenuOpen" 
                        x-cloak 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                        class="absolute bottom-full left-0 right-0 mb-2 p-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 shadow-2xl z-50 space-y-1 backdrop-blur-xl"
                    >
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-white/5">
                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Sesión iniciada</div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ Auth::user()->email }}</div>
                        </div>

                        <a 
                            href="{{ route('profile.edit') }}" 
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-primary transition-colors text-xs font-bold cursor-pointer"
                        >
                            <x-mary-icon name="o-user" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                            <span>Mi Perfil</span>
                        </a>

                        <div class="h-[1px] bg-slate-100 dark:bg-white/5 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button 
                                type="submit" 
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-error hover:bg-error/10 text-xs font-black uppercase tracking-wider transition-colors text-left cursor-pointer"
                            >
                                <x-mary-icon name="o-arrow-right-on-rectangle" class="w-4 h-4 text-error" />
                                <span>Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>

                    <!-- Trigger Button / User Card -->
                    <button 
                        type="button" 
                        @click="userMenuOpen = !userMenuOpen" 
                        class="w-full flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100 dark:bg-white/5 dark:hover:bg-white/10 border border-slate-200/60 dark:border-white/5 transition-all text-left cursor-pointer group"
                        :class="{ 'ring-2 ring-primary/20 bg-slate-100 dark:bg-white/10': userMenuOpen }"
                        title="Opciones de usuario"
                    >
                        <div class="relative shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center border border-slate-200 dark:border-white/10 shadow-sm group-hover:scale-105 transition-transform">
                                <x-mary-icon name="o-user" class="w-5 h-5 text-slate-600 dark:text-slate-300 group-hover:text-primary transition-colors" />
                            </div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-slate-950 rounded-full"></div>
                        </div>
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-100 truncate group-hover:text-primary transition-colors">{{ Auth::user()->name }}</span>
                            <span class="text-[10px] text-[#C4A462] font-bold uppercase tracking-wider truncate mt-0.5">{{ Auth::user()->getRoleNames()->first() ?? 'Usuario' }}</span>
                        </div>
                        <x-mary-icon name="o-chevron-up" class="w-4 h-4 text-slate-400 group-hover:text-primary transition-transform duration-200 shrink-0" ::class="{ 'rotate-180': !userMenuOpen }" />
                    </button>
                </div>

            </x-mary-menu>
        </x-slot:sidebar>

        <!-- Main Content -->
        <x-slot:content class="p-3 sm:p-6 md:p-8 lg:pl-[352px] max-w-full overflow-x-hidden">
            @isset($header)
                <header class="mb-6 sm:mb-8">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-6 bg-[#C4A462] rounded-full"></div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Vista Actual</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 dark:text-white tracking-tight">{{ $header }}</h1>
                </header>
            @endisset

            <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                {{ $slot }}
            </div>
        </x-slot:content>
    </x-mary-main>

    <x-mary-toast />

    @canany(['loans.deliver', 'loans.return', 'expedients.change-location', 'loans.approve'])
        <livewire:global-scanner-modal />

        <script>
            (function () {
                let barcodeBuffer = '';
                let lastKeyTime = Date.now();

                window.addEventListener('keydown', function (e) {
                    // Keyboard shortcuts: Ctrl+K or F2 opens quick scanner modal
                    if ((e.ctrlKey && e.key.toLowerCase() === 'k') || e.key === 'F2') {
                        e.preventDefault();
                        if (window.Livewire) {
                            Livewire.dispatch('open-global-scanner');
                        }
                        return;
                    }

                    // Don't intercept if user is actively typing in the quick scan input
                    const activeElem = document.activeElement;
                    if (activeElem && activeElem.id === 'global-quick-scan-input') {
                        return;
                    }

                    const currentTime = Date.now();
                    const timeDiff = currentTime - lastKeyTime;
                    lastKeyTime = currentTime;

                    if (e.key === 'Enter') {
                        if (barcodeBuffer.length >= 3) {
                            const code = barcodeBuffer.trim();
                            barcodeBuffer = '';
                            if (window.Livewire) {
                                e.preventDefault();
                                Livewire.dispatch('open-global-scanner', { code: code });
                            }
                        } else {
                            barcodeBuffer = '';
                        }
                    } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                        // Hardware scanners send characters in bursts < 60ms apart
                        if (timeDiff > 70) {
                            barcodeBuffer = e.key;
                        } else {
                            barcodeBuffer += e.key;
                        }
                    }
                });
            })();
        </script>
    @endcanany

    @stack('scripts')
</body>
</html>
