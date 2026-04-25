<div>
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

        <div class="p-2 w-72">
            <div class="font-bold text-sm px-2 mb-2 flex justify-between items-center">
                <span>Notificaciones</span>
                <span class="text-xs font-normal text-gray-500">{{ $this->unreadCount }} nuevas</span>
            </div>

            @forelse($this->notifications as $notification)
                <x-mary-list-item :item="$notification" class="hover:bg-base-200 rounded-lg p-2 transition-colors mb-1 border-b border-base-200 last:border-0" no-separator no-hover>
                    <x-slot:avatar>
                        <div class="p-2 rounded-full {{ ($notification->data['type'] ?? '') === 'overdue' ? 'bg-error/10 text-error' : 'bg-primary/10 text-primary' }}">
                            <x-mary-icon name="{{ ($notification->data['type'] ?? '') === 'overdue' ? 'o-exclamation-triangle' : 'o-document-text' }}" class="w-4 h-4" />
                        </div>
                    </x-slot:avatar>
                    <x-slot:value>
                        <div class="text-[11px] font-bold leading-tight line-clamp-2">
                            {{ $notification->data['message'] ?? 'Notificación' }}
                        </div>
                    </x-slot:value>
                    <x-slot:sub-value>
                        <div class="text-[10px] text-gray-500 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </x-slot:sub-value>
                </x-mary-list-item>
            @empty
                <div class="text-center py-6">
                    <x-mary-icon name="o-check-circle" class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    <p class="text-xs text-gray-500">Todo al día</p>
                </div>
            @endforelse

            <div class="mt-2 border-t pt-2">
                <x-mary-button label="Ver todos los préstamos" link="{{ route('loans.index') }}" class="btn-xs btn-ghost w-full justify-center text-primary" />
            </div>
        </div>
    </x-mary-dropdown>
</div>
