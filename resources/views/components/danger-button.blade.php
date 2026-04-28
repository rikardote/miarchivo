<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-8 py-3 bg-error border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-error/90 active:bg-error/80 focus:outline-none focus:ring-4 focus:ring-error/20 shadow-lg shadow-error/20 transition-premium duration-150']) }}>
    {{ $slot }}
</button>
