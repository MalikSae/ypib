@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold text-neutral-600 mb-1.5']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-error ml-1">*</span>
    @endif
</label>
