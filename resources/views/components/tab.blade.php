{{-- Satu tab di dalam <x-tab-list>. Aktif: garis bawah primary. Panelnya ber-id "tab-{name}". --}}
@props(['name', 'count' => null])

<button type="button" role="tab" id="tab-btn-{{ $name }}" aria-controls="tab-{{ $name }}"
        x-bind:aria-selected="(tab === @js($name)).toString()"
        x-bind:tabindex="tab === @js($name) ? 0 : -1"
        x-on:click="tab = @js($name)"
        x-bind:class="tab === @js($name) ? 'border-primary-600 text-primary-700' : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-800'"
        {{ $attributes->merge(['class' => 'inline-flex h-11 shrink-0 items-center gap-2 whitespace-nowrap rounded-t-sm border-b-2 px-1 text-sm font-semibold transition-colors motion-reduce:transition-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500']) }}>
    <span>{{ $slot }}</span>
    @if($count !== null)
        <span class="text-xs font-medium" x-bind:class="tab === @js($name) ? 'text-primary-700' : 'text-neutral-400'">{{ $count }}</span>
    @endif
</button>
