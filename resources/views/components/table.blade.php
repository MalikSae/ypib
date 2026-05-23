<div class="overflow-hidden bg-white shadow-sm ring-1 ring-neutral-200 sm:rounded-lg">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-neutral-200']) }}>
            {{ $slot }}
        </table>
    </div>
</div>
