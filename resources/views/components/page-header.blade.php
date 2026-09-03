@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'o-sparkles',
])

<div {{ $attributes->class(['flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/60 dark:border-white/5']) }}>
    <div>
        <div class="flex items-center gap-2.5">
            <div class="p-2.5 rounded-2xl bg-gradient-to-br from-[#0F1E36] to-[#1E3A8A] text-[#C4A462] shadow-md shadow-[#0F1E36]/20">
                <x-mary-icon :name="$icon" class="w-6 h-6" />
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>

    @isset($actions)
        <div class="flex items-center gap-2.5">
            {{ $actions }}
        </div>
    @endisset
</div>
