<div wire:poll.60s class="relative z-[100]">
    <x-mary-dropdown right>
        <x-slot:trigger class="btn btn-ghost btn-circle btn-sm sm:btn-md !h-9 !w-9 sm:!h-10 sm:!w-10">
            <div class="relative flex items-center justify-center" wire:click="markAsRead">
                <x-mary-icon name="o-bell" class="w-5 h-5 cursor-pointer text-slate-200 hover:text-[#C4A462] transition-colors" />
                @if($this->unreadCount > 0)
                    <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-black px-1 rounded-full border-2 border-[#073256] min-w-[16px] h-[16px] flex items-center justify-center shadow-md">
                        {{ $this->unreadCount }}
                    </span>
                @endif
            </div>
        </x-slot:trigger>

        <div class="p-4 w-80 md:w-96">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-white/5">
                <div>
                    <h4 class="text-base font-black text-slate-900 dark:text-white dark:text-white tracking-tight">Notificaciones</h4>
                    <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-0.5">Últimas actualizaciones</p>
                </div>
                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[9px] font-black rounded-lg uppercase border border-primary/20 shadow-sm">{{ $this->unreadCount }} nuevas</span>
            </div>

            <div class="max-h-[400px] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                @forelse($this->notifications as $notification)
                    <div class="flex gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 hover:bg-white dark:hover:bg-white/10 transition-premium group relative overflow-hidden">
                        @if(!$notification->read_at)
                            <div class="absolute top-0 right-0 w-1.5 h-full bg-primary"></div>
                        @endif
                        <div class="flex-shrink-0">
                            <div class="p-2.5 rounded-xl {{ ($notification->data['type'] ?? '') === 'error' ? 'bg-rose-500/10 text-rose-600' : (($notification->data['type'] ?? '') === 'success' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-primary/10 text-primary') }}">
                                <x-mary-icon name="{{ ($notification->data['type'] ?? '') === 'error' ? 'o-exclamation-triangle' : (($notification->data['type'] ?? '') === 'success' ? 'o-check-circle' : 'o-document-text') }}" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-[11px] font-bold text-slate-800 dark:text-slate-100 dark:text-slate-200 leading-snug">
                                {{ $notification->data['message'] ?? 'Notificación' }}
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 dark:text-slate-400 uppercase tracking-tighter">{{ $notification->created_at->diffForHumans() }}</span>
                                @if(isset($notification->data['expedient_code']))
                                    <span class="text-[9px] font-black text-primary uppercase tracking-widest bg-primary/5 px-2 py-0.5 rounded-md border border-primary/10">{{ $notification->data['expedient_code'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 opacity-30">
                            <x-mary-icon name="o-bell-slash" class="w-8 h-8" />
                        </div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">No hay alertas pendientes</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-white/5">
                <x-mary-button label="GESTIONAR PRÉSTAMOS" icon="o-list-bullet" link="{{ route('loans.index') }}" class="btn-ghost w-full justify-center text-primary font-black text-[10px] tracking-widest hover:bg-primary/5 rounded-xl h-12" />
            </div>
        </div>
    </x-mary-dropdown>
</div>
