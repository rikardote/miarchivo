<x-guest-layout>
    <div class="mb-10">
        <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Bienvenido de nuevo</h3>
        <p class="text-slate-500 font-medium">Ingresa tus credenciales para acceder al sistema.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <x-mary-input 
            label="Correo Electrónico" 
            name="email" 
            type="email" 
            value="{{ old('email') }}" 
            icon="o-envelope" 
            required 
            autofocus 
            autocomplete="username"
            class="rounded-2xl border-slate-200 focus:border-primary/50 h-14" />

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <label class="text-sm font-bold text-slate-700">Contraseña</label>
            </div>
            <x-mary-input 
                name="password" 
                type="password" 
                icon="o-lock-closed" 
                required 
                autocomplete="current-password"
                class="rounded-2xl border-slate-200 focus:border-primary/50 h-14" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <x-mary-checkbox label="Mantener sesión iniciada" name="remember" class="checkbox-primary" />
        </div>

        <div class="pt-4">
            <x-mary-button 
                label="Iniciar Sesión" 
                icon="o-arrow-right-on-rectangle" 
                class="btn-primary w-full h-14 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-primary/20 hover:scale-[1.02] transition-premium" 
                type="submit" />
        </div>
    </form>
</x-guest-layout>
