@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-sm text-error mt-1.5']) }}>
        @foreach ((array) $messages as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif
