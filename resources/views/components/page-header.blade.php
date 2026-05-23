@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6']) }}>
    <div>
        <h2 class="font-bold text-2xl text-neutral-900 leading-tight">
            {{ $title }}
        </h2>
        @if($description)
            <p class="mt-1 text-sm text-neutral-500">{{ $description }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
