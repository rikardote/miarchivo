<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Archivo') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen">
    <x-mary-nav sticky class="glass border-b border-slate-200 dark:border-white/10 z-[60] px-6 !h-20">
        <x-slot:brand>
            <div class="flex items-center gap-3 group cursor-pointer">
                <img src="{{ asset('60issste.png') }}" alt="Logo" class="h-12 w-auto object-contain" />
                <div class="flex flex-col">
                    <div class="font-black text-2xl tracking-tighter text-slate-900 dark:text-white dark:text-white leading-none">
                        Archivo<span class="text-primary">.</span>
                    </div>
                    <div class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400 dark:text-slate-400 dark:text-slate-500 mt-1">ISSSTE BAJA CALIFORNIA</div>
                </div>
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <div class="p-1 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center transition-premium border border-transparent dark:border-white/5">
                    <livewire:notifications-bell />
                </div>
                
                <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>

                <x-mary-dropdown right class="btn-ghost !h-14 px-3 rounded-2xl hover:bg-white dark:hover:bg-white/5 transition-premium border border-transparent hover:border-slate-100 dark:hover:border-white/5">
                    <x-slot:label>
                        <div class="flex items-center gap-4">
                            <div class="hidden md:flex flex-col items-end">
                                <span class="text-sm font-black text-slate-900 dark:text-white dark:text-slate-100 leading-none">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] text-primary font-bold uppercase tracking-widest mt-1.5 opacity-80">{{ Auth::user()->getRoleNames()->first() }}</span>
                            </div>
                            <div class="relative group-hover:scale-110 transition-premium">
                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-white/10 shadow-sm">
                                    <x-mary-icon name="o-user" class="w-6 h-6 text-slate-500 dark:text-slate-400 dark:text-slate-400" />
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-4 border-white dark:border-slate-950 rounded-full shadow-sm"></div>
                            </div>
                        </div>
                    </x-slot:label>
                    
                    <div class="p-2 min-w-[200px]">
                        <x-mary-menu-item title="Mi Perfil" icon="o-user" link="/profile" class="rounded-xl text-sm font-bold" />
                        <x-mary-menu-separator class="my-2 opacity-50" />
                        <x-mary-menu-item icon="o-arrow-right-on-rectangle" class="text-error font-black rounded-xl hover:bg-error/10">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="submit" class="w-full text-left uppercase text-[10px] tracking-widest">Cerrar Sesión</button>
                            </form>
                        </x-mary-menu-item>
                    </div>
                </x-mary-dropdown>

                <label for="main-drawer" class="lg:hidden btn btn-ghost btn-circle text-slate-600 dark:text-slate-300 dark:text-white">
                    <x-mary-icon name="o-bars-3" />
                </label>
            </div>
        </x-slot:actions>
    </x-mary-nav>

    <x-mary-main with-nav full-width>
        <!-- Sidebar -->
        <x-slot:sidebar drawer="main-drawer" collapsible class="sidebar-glass z-50">
            <div class="hidden lg:flex flex-col p-10 mb-6">
                <div class="font-black text-[11px] tracking-[0.5em] text-slate-500 dark:text-slate-400 dark:text-slate-400 dark:text-white/20 uppercase mb-4">MENÚ PRINCIPAL</div>
                <div class="h-[4px] w-12 bg-primary rounded-full shadow-[0_4px_12px_rgba(var(--color-primary),0.3)]"></div>
            </div>

            <x-mary-menu active-class="sidebar-item-active" class="px-6 space-y-3">
                <x-mary-menu-item title="Dashboard" icon="o-chart-pie" link="{{ route('dashboard') }}" active="{{ request()->routeIs('dashboard') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 py-4 transition-premium font-black text-sm" />

                <div class="px-6 py-6 mt-10 mb-4 text-[10px] font-black text-slate-300 dark:text-white/10 uppercase tracking-[0.4em] border-t border-slate-100 dark:border-white/5 pt-10">Gestión Operativa</div>

                @can('expedients.view')
                <x-mary-menu-sub title="Expedientes" icon="o-folder" open="{{ request()->routeIs('expedients.*') }}" class="text-slate-600 dark:text-white/70 font-black text-sm">
                    <x-mary-menu-item title="Buscador Central" icon="o-magnifying-glass" link="{{ route('expedients.index') }}" active="{{ request()->routeIs('expedients.index') }}" class="text-xs font-bold py-3" />
                    <x-mary-menu-item title="Escáner Inteligente" icon="o-qr-code" link="{{ route('expedients.scanner') }}" active="{{ request()->routeIs('expedients.scanner') }}" class="text-xs font-bold py-3" />
                    @can('expedients.change-location')
                        <x-mary-menu-item title="Auditoría de Control" icon="o-check-badge" link="{{ route('expedients.audit') }}" active="{{ request()->routeIs('expedients.audit') }}" class="text-xs font-bold py-3" />
                    @endcan
                    @can('expedients.create')
                        <x-mary-menu-item title="Alta de Expediente" icon="o-plus" link="{{ route('expedients.create') }}" active="{{ request()->routeIs('expedients.create') }}" class="text-xs font-bold py-3" />
                    @endcan
                </x-mary-menu-sub>
                @endcan

                <x-mary-menu-sub title="Préstamos" icon="o-document-text" open="{{ request()->routeIs('loans.*') }}" class="text-slate-600 dark:text-white/70 font-black text-sm">
                    <x-mary-menu-item title="Bandeja Personal" icon="o-inbox" link="{{ route('loans.index', ['mine' => 1]) }}" active="{{ request()->fullUrlIs(route('loans.index', ['mine' => 1])) }}" class="text-xs font-bold py-3" />
                    @can('loans.approve')
                        <x-mary-menu-item title="Carga Masiva" icon="o-rectangle-stack" link="{{ route('loans.bulk') }}" active="{{ request()->routeIs('loans.bulk') }}" class="text-xs font-bold py-3" />
                        <x-mary-menu-item title="Mesa de Control" icon="o-clipboard-document-check" link="{{ route('loans.index') }}" active="{{ request()->routeIs('loans.index') && !request()->has('mine') }}" class="text-xs font-bold py-3" />
                    @endcan
                </x-mary-menu-sub>

                @canany(['employees.view', 'locations.view', 'users.view'])
                    <div class="px-6 py-6 mt-10 mb-4 text-[10px] font-black text-slate-300 dark:text-white/10 uppercase tracking-[0.4em] border-t border-slate-100 dark:border-white/5 pt-10">Sistema</div>
                    
                    @can('employees.view')
                        <x-mary-menu-item title="Capital Humano" icon="o-users" link="{{ route('employees.index') }}" active="{{ request()->routeIs('employees.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 transition-premium font-black text-sm" />
                    @endcan

                    @can('locations.view')
                        <x-mary-menu-item title="Mapa de Archivo" icon="o-map-pin" link="{{ route('locations.index') }}" active="{{ request()->routeIs('locations.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 transition-premium font-black text-sm" />
                    @endcan

                    @can('users.view')
                        <x-mary-menu-item title="Seguridad" icon="o-shield-check" link="{{ route('users.index') }}" active="{{ request()->routeIs('users.*') }}" class="rounded-3xl text-slate-600 dark:text-slate-300 dark:text-white/50 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-white/5 transition-premium font-black text-sm" />
                    @endcan
                @endcanany

                <div class="mt-auto pt-14 pb-8">
                    <x-mary-theme-toggle darkTheme="dark" lightTheme="light" class="btn btn-ghost w-full justify-start text-slate-500 dark:text-slate-400 dark:text-slate-400 dark:text-white/20 hover:text-primary dark:hover:text-white rounded-3xl border border-slate-200 dark:border-white/5 hover:bg-white dark:hover:bg-white/5 transition-premium h-14 px-6 font-black text-[10px] uppercase tracking-widest" />
                </div>
            </x-mary-menu>
        </x-slot:sidebar>

        <!-- Main Content -->
        <x-slot:content class="!p-4 md:!p-8">
            @isset($header)
                <header class="mb-8">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-6 bg-primary rounded-full"></div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 dark:text-slate-400">Vista Actual</span>
                    </div>
                    <h1 class="text-4xl font-black text-slate-800 dark:text-slate-100 dark:text-white tracking-tight">{{ $header }}</h1>
                </header>
            @endisset

            <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                {{ $slot }}
            </div>
        </x-slot:content>
    </x-mary-main>

    <x-mary-toast />
    @stack('scripts')
</body>
</html>
