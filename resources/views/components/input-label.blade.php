@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-1.5 block text-sm font-semibold text-neutral-700']) }}>
    {{ $value ?? $slot }}
</label>