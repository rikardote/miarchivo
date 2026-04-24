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

    <!-- Alpine Plugins (Livewire 3 compatibility) -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen bg-base-200">
    <x-mary-nav sticky class="lg:hidden">
        <x-slot:brand>
            <div class="font-bold text-xl ml-2">Archivo</div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden">
                <x-mary-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-mary-nav>

    <x-mary-main with-nav full-width>
        <!-- Sidebar -->
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100">
            <div class="hidden lg:flex items-center justify-between p-4 mb-4 border-b border-base-300">
                <div class="font-bold text-xl tracking-widest text-primary">ARCHIVO</div>
            </div>

            <x-mary-menu activate-by-route>
                @if(Auth::user() && Auth::user()->name)
                    <x-mary-menu-item title="{{ Auth::user()->name }}" icon="o-user" link="/profile" class="mb-4 text-sm font-medium" />
                @endif
                
                <x-mary-menu-item title="Dashboard" icon="o-chart-pie" link="{{ route('dashboard') }}" />

                <x-mary-menu-sub title="Expedientes" icon="o-folder">
                    <x-mary-menu-item title="Buscar" icon="o-magnifying-glass" link="{{ route('expedients.index') }}" />
                    @can('expedients.create')
                        <x-mary-menu-item title="Crear Nuevo" icon="o-plus" link="#" />
                    @endcan
                </x-mary-menu-sub>

                <x-mary-menu-sub title="Préstamos" icon="o-document-text">
                    <x-mary-menu-item title="Mis Solicitudes" icon="o-inbox" link="{{ route('loans.index') }}" />
                    @can('loans.approve')
                        <x-mary-menu-item title="Gestión de Préstamos" icon="o-clipboard-document-check" link="{{ route('loans.index') }}" />
                    @endcan
                </x-mary-menu-sub>

                <x-mary-menu-item title="Empleados" icon="o-users" link="#" />

                @can('locations.view')
                    <x-mary-menu-item title="Ubicaciones" icon="o-map-pin" link="#" />
                @endcan

                @can('users.view')
                    <x-mary-menu-item title="Usuarios" icon="o-shield-check" link="#" />
                @endcan

                <x-mary-menu-separator />

                <x-mary-theme-toggle darkTheme="dark" lightTheme="light" class="btn btn-sm btn-ghost w-full justify-start mt-2" />
                
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-error btn-outline w-full justify-start">
                        <x-mary-icon name="o-arrow-right-on-rectangle" /> Salir
                    </button>
                </form>
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
