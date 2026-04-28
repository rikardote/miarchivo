<div wire:poll.60s class="relative z-[100]">
    <x-mary-dropdown right>
        <x-slot:label>
            <div class="relative inline-block mr-2" wire:click="markAsRead">
                <x-mary-icon name="o-bell" class="w-6 h-6 cursor-pointer hover:text-primary transition-colors" />
                @if($this->unreadCount > 0)
                    <span class="absolute -top-1 -right-1 bg-error text-white text-[10px] font-bold px-1 rounded-full border-2 border-base-100 min-w-[18px] text-center">
                        {{ $this->unreadCount }}
                    </span>
                @endif
            </div>
        </x-slot:label>

        <div class="p-2 w-80 md:w-96">
            <div class="font-bold text-sm px-2 mb-2 flex justify-between items-center border-b pb-2 border-base-200">
                <span>Notificaciones</span>
                <span class="text-[10px] font-normal px-2 py-0.5 bg-primary/10 text-primary rounded-full">{{ $this->unreadCount }} nuevas</span>
            </div>

            <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                @forelse($this->notifications as $notification)
                    <x-mary-list-item :item="$notification" class="hover:bg-base-200 rounded-lg p-2 transition-colors mb-1" no-separator no-hover>
                        <x-slot:avatar>
                            <div class="p-2 rounded-full {{ ($notification->data['type'] ?? '') === 'error' ? 'bg-error/10 text-error' : (($notification->data['type'] ?? '') === 'success' ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary') }}">
                                <x-mary-icon name="{{ ($notification->data['type'] ?? '') === 'error' ? 'o-xclamation-triangle' : (($notification->data['type'] ?? '') === 'success' ? 'o-check-circle' : 'o-document-text') }}" class="w-4 h-4" />
                            </div>
                        </x-slot:avatar>
                        <x-slot:value>
                            <div class="text-[11px] font-bold leading-tight">
                                {{ $notification->data['message'] ?? 'Notificación' }}
                            </div>
                        </x-slot:value>
                        <x-slot:sub-value>
                            <div class="text-[10px] text-gray-500 mt-1 flex justify-between items-center">
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                <span class="text-[9px] opacity-50">{{ $notification->data['expedient_code'] ?? '' }}</span>
                            </div>
                        </x-slot:sub-value>
                    </x-mary-list-item>
                @empty
                    <div class="text-center py-10">
                        <x-mary-icon name="o-bell-slash" class="w-10 h-10 text-base-300 mx-auto mb-2" />
                        <p class="text-xs text-gray-400">No tienes notificaciones pendientes</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-2 border-t border-base-200 pt-2 px-1">
                <x-mary-button label="Ver todos los préstamos" icon="o-list-bullet" link="{{ route('loans.index') }}" class="btn-xs btn-ghost w-full justify-center text-primary font-bold" />
            </div>
        </div>
    </x-mary-dropdown>
</div>
