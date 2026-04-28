@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-black uppercase tracking-widest text-slate-500 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
