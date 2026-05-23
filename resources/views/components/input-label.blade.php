@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-neutral-900 mb-2']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-error ml-1">*</span>
    @endif
</label>
