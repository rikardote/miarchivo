<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-8 py-3 bg-primary border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-primary/90 focus:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-4 focus:ring-primary/20 shadow-lg shadow-primary/20 transition-premium duration-150']) }}>
    {{ $slot }}
</button>
