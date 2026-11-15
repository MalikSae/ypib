{{--
    Radio standar. Tanpa label: hanya kotaknya.
    Dengan label: <x-radio name="jawaban" value="a" :checked="old('jawaban') === 'a'" label="A" />
    Atribut Alpine yang terikat ditulis dengan "x-bind:" (misal x-bind:checked="allSelected"), bukan ":".
--}}
@props(['label' => null, 'error' => false])

@php
    $input = $attributes->merge(['type' => 'radio', 'class' => 'h-4 w-4 shrink-0 rounded-full border-neutral-300 text-primary-600 cursor-pointer focus:ring-2 focus:ring-primary-500/20 focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50 ' . ($error ? 'border-error' : '')]);
@endphp

@if($label !== null || ! $slot->isEmpty())
    <label class="inline-flex items-center gap-2 min-h-11 md:min-h-9 text-sm text-neutral-700 cursor-pointer has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
        <input {{ $input }}>
        <span>{{ $label ?? $slot }}</span>
    </label>
@else
    <input {{ $input }}>
@endif
