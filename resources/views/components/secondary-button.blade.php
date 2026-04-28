<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-8 py-3 bg-white dark:bg-slate-900 border border-slate-100 dark:border-white/5 rounded-2xl font-black text-xs text-slate-600 dark:text-slate-300 uppercase tracking-widest shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-100 transition-premium duration-150 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
