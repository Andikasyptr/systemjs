@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm focus:border-red-500 focus:ring-red-500']) }}>