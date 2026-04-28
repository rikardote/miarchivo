<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Archivo') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen bg-base-200">
    <x-mary-nav sticky class="bg-base-100 border-b border-base-300 z-[60]">
        <x-slot:brand>
            <div class="font-bold text-xl ml-2">Archivo</div>
        </x-slot:brand>
        <x-slot:actions>
            <div class="flex items-center gap-2 md:gap-4">
                <livewire:notifications-bell />
                
                <x-mary-dropdown right class="btn-ghost">
                    <x-slot:label>
                        <div class="flex items-center gap-3">
                            <div class="hidden md:flex flex-col items-end">
                                <span class="text-xs font-bold leading-none">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] opacity-60 uppercase tracking-tighter">{{ Auth::user()->getRoleNames()->first() }}</span>
                            </div>
                            <x-mary-icon name="o-user-circle" class="w-8 h-8 opacity-70" />
                        </div>
                    </x-slot:label>
                    
                    <x-mary-menu-item title="Mi Perfil" icon="o-user" link="/profile" />
                    <x-mary-menu-separator />
                    <x-mary-menu-item icon="o-arrow-right-on-rectangle" class="text-error">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="w-full text-left">Cerrar Sesión</button>
                        </form>
                    </x-mary-menu-item>
                </x-mary-dropdown>

                <label for="main-drawer" class="lg:hidden btn btn-ghost btn-sm btn-circle">
                    <x-mary-icon name="o-bars-3" />
                </label>
            </div>
        </x-slot:actions>
    </x-mary-nav>

    <x-mary-main with-nav full-width>
        <!-- Sidebar -->
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 z-50">
            <div class="hidden lg:flex items-center p-4 mb-4 border-b border-base-300">
                <div class="font-bold text-xl tracking-widest text-primary">ARCHIVO</div>
            </div>

            <x-mary-menu activate-by-route>
                <x-mary-menu-item title="Dashboard" icon="o-chart-pie" link="{{ route('dashboard') }}" />

                @can('expedients.view')
                <x-mary-menu-sub title="Expedientes" icon="o-folder">
                    <x-mary-menu-item title="Buscar" icon="o-magnifying-glass" link="{{ route('expedients.index') }}" />
                    <x-mary-menu-item title="Escanear QR" icon="o-qr-code" link="{{ route('expedients.scanner') }}" />
                    @can('expedients.change-location')
                        <x-mary-menu-item title="Auditoría / Inventario" icon="o-check-badge" link="{{ route('expedients.audit') }}" />
                    @endcan
                    @can('expedients.create')
                        <x-mary-menu-item title="Crear Nuevo" icon="o-plus" link="{{ route('expedients.create') }}" />
                    @endcan
                </x-mary-menu-sub>
                @endcan

                <x-mary-menu-sub title="Préstamos" icon="o-document-text">
                    <x-mary-menu-item title="Mis Solicitudes" icon="o-inbox" link="{{ route('loans.index', ['mine' => 1]) }}" />
                    @can('loans.approve')
                        <x-mary-menu-item title="Préstamo Masivo" icon="o-rectangle-stack" link="{{ route('loans.bulk') }}" />
                        <x-mary-menu-item title="Gestión de Préstamos" icon="o-clipboard-document-check" link="{{ route('loans.index') }}" />
                    @endcan
                </x-mary-menu-sub>

                @can('employees.view')
                    <x-mary-menu-item title="Empleados" icon="o-users" link="{{ route('employees.index') }}" />
                @endcan

                @can('locations.view')
                    <x-mary-menu-item title="Ubicaciones" icon="o-map-pin" link="{{ route('locations.index') }}" />
                @endcan

                @can('users.view')
                    <x-mary-menu-item title="Usuarios" icon="o-shield-check" link="{{ route('users.index') }}" />
                @endcan

                <x-mary-menu-separator />

                <x-mary-theme-toggle darkTheme="dark" lightTheme="light" class="btn btn-sm btn-ghost w-full justify-start mt-2" />
            </x-mary-menu>
        </x-slot:sidebar>

        <!-- Main Content -->
        <x-slot:content>
            @isset($header)
                <header class="mb-6">
                    <h1 class="text-2xl font-bold text-base-content">{{ $header }}</h1>
                </header>
            @endisset

            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    <x-mary-toast />
</body>
</html>
