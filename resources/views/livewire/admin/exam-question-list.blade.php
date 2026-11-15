<div>
{{-- FILTER & SEARCH --}}
<div class="mb-6 bg-white p-4 rounded-2xl border border-neutral-200 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="w-full sm:w-1/2 md:w-1/3">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <label for="question_search" class="sr-only">Cari pertanyaan</label>
            <x-text-input type="search" id="question_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari pertanyaan" class="pl-10" />
        </div>
    </div>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8 transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-12">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pertanyaan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kunci Jawaban</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($questions as $index => $q)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group" wire:key="question-{{ $q->id }}">
                    <td class="px-6 py-4 text-sm text-neutral-500">{{ $questions->firstItem() + $index }}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-neutral-900 group-hover:text-primary-700 transition-colors line-clamp-2" title="{{ $q->question_text }}">{{ Str::limit($q->question_text, 100) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-neutral-600 font-semibold uppercase">Opsi {{ $q->correct_option }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <x-badge-status :active="$q->is_active" />
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.bank-soal.edit', $q)" />
                            
                            <x-table-action-delete :action="route('admin.bank-soal.destroy', $q)" text="Apakah Anda yakin ingin menghapus soal ini?" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-sm text-neutral-400">
                        {{ trim($search) !== '' ? 'Tidak ada soal yang cocok.' : 'Belum ada soal terdaftar.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-8">
    {{ $questions->links('livewire.admin.pagination') }}
</div>
</div>
