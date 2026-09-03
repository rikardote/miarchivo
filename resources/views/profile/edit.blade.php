<x-app-layout>
    <x-page-header title="Perfil de Usuario" subtitle="Preferencias de cuenta y apariencia del sistema" icon="o-user" class="mb-8" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <div class="p-8 bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-[2rem] border-none">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">
                                Preferencias de Interfaz
                            </h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Personaliza la apariencia visual del sistema.
                            </p>
                        </header>

                        <div class="mt-6 flex flex-col gap-4">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500">Modo de Visualización</label>
                            <x-mary-theme-toggle darkTheme="dark" lightTheme="light" class="btn btn-outline border-slate-200 dark:border-white/5 rounded-2xl h-14 justify-start px-6 font-bold uppercase text-[10px] tracking-widest hover:bg-slate-50 dark:hover:bg-white/5 transition-premium" />
                        </div>
                    </section>
                </div>
            </div>

            <div class="p-8 bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-[2rem] border-none">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-8 bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-[2rem] border-none">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
