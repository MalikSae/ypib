@extends('layouts.admin')
@section('title', isset($question) ? 'Edit Soal' : 'Tambah Soal')
@section('page-title', isset($question) ? 'Edit Soal' : 'Tambah Soal')

@section('content')
<div class="max-w-4xl">

    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.bank-soal.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0">
            <i class="ti ti-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">{{ isset($question) ? 'Edit Soal' : 'Tambah Soal' }}</h1>
            <p class="text-sm text-neutral-500">{{ isset($question) ? 'Ubah data soal pilihan ganda' : 'Buat soal pilihan ganda baru' }}</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ isset($question) ? route('admin.bank-soal.update', $question) : route('admin.bank-soal.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if(isset($question))
                @method('PUT')
            @endif

            <div class="mb-6">
                <x-input-label for="question_text" value="Pertanyaan" required="true" />
                <x-textarea id="question_text" name="question_text" rows="4" required :error="$errors->has('question_text')">{{ old('question_text', $question->question_text ?? '') }}</x-textarea>
                <x-input-error :messages="$errors->get('question_text')" />
            </div>

            <div class="space-y-4 mb-6">
                <h3 class="text-sm font-semibold text-neutral-900 border-b pb-2">Pilihan Jawaban</h3>
                
                @foreach(['a', 'b', 'c', 'd'] as $opt)
                <div class="flex items-start gap-4">
                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="correct_option" value="{{ $opt }}" class="w-5 h-5 accent-primary-600" required {{ old('correct_option', $question->correct_option ?? '') == $opt ? 'checked' : '' }}>
                            <span class="font-bold uppercase text-neutral-700 w-4">{{ $opt }}</span>
                        </label>
                    </div>
                    <div class="flex-grow">
                        <x-text-input type="text" name="option_{{ $opt }}" value="{{ old('option_'.$opt, $question->{'option_'.$opt} ?? '') }}" required placeholder="Pilihan {{ strtoupper($opt) }}" :error="$errors->has('option_'.$opt)" />
                        <x-input-error :messages="$errors->get('option_'.$opt)" />
                    </div>
                </div>
                @endforeach
                <x-input-error :messages="$errors->get('correct_option')" />
                <p class="text-xs text-neutral-500 mt-2">Pilih radio button untuk menentukan mana jawaban yang benar.</p>
            </div>

            <div class="mb-8">
                <x-input-label for="is_active" value="Status" />
                <div class="flex items-center h-12">
                    <label class="flex items-center cursor-pointer gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $question->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-primary-600 cursor-pointer">
                        <span class="text-sm font-medium text-neutral-600">Soal Aktif (Tampil di Tes)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.bank-soal.index') }}" class="decoration-none">
                    <x-button color="neutral" variant="ghost" type="button">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">
                    <span x-show="!submitting">{{ isset($question) ? 'Simpan Perubahan' : 'Tambah Soal' }}</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <i class="ti ti-loader animate-spin"></i> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
