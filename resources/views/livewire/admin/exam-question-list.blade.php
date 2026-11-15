<div>
    {{-- Cari langsung menyaring. --}}
    <div class="mb-4 bg-white p-4 rounded-lg border border-neutral-200 flex flex-col md:flex-row md:items-center gap-3">
        <div class="relative w-full md:w-1/2">
            <x-input-label for="question_search" value="Cari pertanyaan" class="sr-only" />
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </div>
            <x-text-input type="search" id="question_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari pertanyaan" class="pl-10" />
        </div>
        @if(trim($search) !== '')
            <x-button color="neutral" variant="ghost" size="sm" wire:click="$set('search', '')" wire:loading.attr="disabled" wire:target="search">Hapus filter</x-button>
        @endif
    </div>

    <div class="transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        {{-- Mobile: daftar kartu --}}
        <div class="md:hidden space-y-3">
            @forelse($questions as $index => $question)
                <div class="bg-white rounded-lg border border-neutral-200 p-4" wire:key="question-card-{{ $question->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium text-neutral-900 line-clamp-2" title="{{ $question->question_text }}">{{ $questions->firstItem() + $index }}. {{ $question->question_text }}</p>
                        <x-badge-status :active="$question->is_active" />
                    </div>
                    <p class="mt-2 text-xs text-neutral-500">Kunci jawaban: opsi {{ strtoupper($question->correct_option) }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <x-button :href="route('admin.bank-soal.edit', $question)" color="neutral" variant="outline">Edit soal</x-button>
                        <div class="flex justify-end items-center"><x-table-action-delete :action="route('admin.bank-soal.destroy', $question)" text="Apakah Anda yakin ingin menghapus soal ini?" /></div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-neutral-200 px-4 py-10 text-center text-sm text-neutral-500">{{ trim($search) !== '' ? 'Tidak ada soal yang cocok.' : 'Belum ada soal terdaftar.' }}</div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden md:block bg-white rounded-lg border border-neutral-200 overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Pertanyaan</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Kunci jawaban</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($questions as $index => $question)
                        <tr class="align-middle hover:bg-neutral-50" wire:key="question-{{ $question->id }}">
                            <td class="px-4 py-3 text-neutral-500">{{ $questions->firstItem() + $index }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-neutral-900 line-clamp-2 max-w-xl" title="{{ $question->question_text }}">{{ $question->question_text }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-neutral-700">Opsi {{ strtoupper($question->correct_option) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap"><x-badge-status :active="$question->is_active" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-table-action-edit :href="route('admin.bank-soal.edit', $question)" title="Edit soal" />
                                    <x-table-action-delete :action="route('admin.bank-soal.destroy', $question)" text="Apakah Anda yakin ingin menghapus soal ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-16 text-center text-sm text-neutral-500">{{ trim($search) !== '' ? 'Tidak ada soal yang cocok.' : 'Belum ada soal terdaftar.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($questions->hasPages())
        <div class="mt-4">{{ $questions->links('livewire.admin.pagination') }}</div>
    @endif
</div>
