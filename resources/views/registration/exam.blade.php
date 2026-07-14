@extends('layouts.landing')
@section('title', 'Tes Tulis Online — Pendaftaran YPIB')

@section('content')
<section class="py-16 bg-[#F1F4F7] min-h-[calc(100vh-64px)]">
<div class="pub-container">
<div class="max-w-3xl mx-auto pb-12">

    {{-- Back Link --}}
    <div class="mb-6">
        <a href="{{ route('registration.status') }}" class="inline-flex items-center gap-2 text-sm font-medium text-neutral-500 hover:text-primary-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Status Pendaftaran
        </a>
    </div>

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900 mb-1">Tes Tulis Online</h1>
            @if($isCompleted)
                <p class="text-sm text-neutral-500">Anda telah menyelesaikan tes tulis online.</p>
            @else
                <p class="text-sm text-neutral-500">Kerjakan semua soal di bawah ini, tidak ada batas waktu. Jawaban tersimpan otomatis.</p>
            @endif
        </div>
        @if(!$isCompleted)
        <div class="flex-shrink-0 w-full sm:w-auto">
            <div class="bg-white px-4 py-2 rounded-full border border-neutral-200 text-sm font-semibold text-neutral-700 shadow-sm flex items-center gap-2 w-full sm:w-fit justify-center" x-data="examProgress()" x-init="initProgress({{ $answers->count() }}, {{ $answers->whereNotNull('selected_option')->count() }})" @answer-saved.window="updateProgress()">
                Progress: <span x-text="answered" class="text-primary-700"></span> <span class="text-neutral-400 font-medium">/ {{ $answers->count() }} soal</span>
            </div>
        </div>
        @endif
    </div>

    @if(session('error'))
    <div class="pub-flash-error mb-6">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    @if($isCompleted)
    <div class="bg-white border border-neutral-200 rounded-xl shadow-sm p-6 text-center">
        <div class="w-20 h-20 bg-success-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2 class="text-base font-semibold text-neutral-900 mb-2">Tes Tulis Online Selesai</h2>
        
        <div class="inline-block bg-neutral-50 border border-neutral-200 rounded-xl px-8 py-6 mb-6 mt-4">
            <p class="text-sm text-neutral-500 font-medium mb-1">Hasil Kualifikasi Anda</p>
            <div class="text-3xl font-black text-primary-700 uppercase tracking-wide">
                {{ $session->result_label === 'sangat_baik' ? 'Sangat Baik' : 'Baik' }}
            </div>
        </div>
        
        <p class="text-sm text-neutral-600">Admin akan meninjau hasil Anda dan menghubungi Anda untuk tahap interview selanjutnya.</p>
    </div>
    @else
    
    <div class="bg-white border border-neutral-200 rounded-xl shadow-sm p-6 sm:p-10" x-data="{ showConfirmModal: false }">
        <form action="{{ route('registration.exam.submit') }}" method="POST" id="examForm" x-ref="examForm" @submit.prevent="showConfirmModal = true">
            @csrf
            
            <div>
                @foreach($answers as $index => $answer)
                <div style="margin-bottom: {{ $loop->last ? '0' : '32px' }};" x-data="questionCard('{{ $answer->id }}', '{{ $answer->selected_option }}')">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary-50 text-primary-700 font-bold flex items-center justify-center shrink-0 border border-primary-100">
                                {{ $index + 1 }}
                            </div>
                            <div class="pt-1 flex-1">
                                <h3 class="text-base font-semibold text-neutral-900 flex-1 leading-relaxed">{{ $answer->question->question_text }}</h3>
                            </div>
                        </div>
                        
                        <!-- Indicator removed per user request -->
                    </div>
                    
                    <div class="pl-12 space-y-3">
                        @foreach(['a', 'b', 'c', 'd'] as $opt)
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border transition-colors cursor-pointer"
                            :class="selected === '{{ $opt }}' ? 'border-primary-500 bg-primary-50/50' : 'border-neutral-200 hover:border-neutral-300 hover:bg-neutral-50'">
                            <div class="shrink-0 flex items-center">
                                <input type="radio" name="answers[{{ $answer->id }}]" value="{{ $opt }}" class="w-5 h-5 accent-primary-600" 
                                    x-model="selected" @change="saveAnswer('{{ $opt }}')">
                            </div>
                            <div class="text-sm text-neutral-700 leading-tight">
                                <span class="font-bold uppercase mr-1">{{ $opt }}.</span> {{ $answer->question->{'option_'.$opt} }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                
                @if(!$loop->last)
                    <hr style="margin-top: 32px; margin-bottom: 32px; border: 0; border-top: 1px solid #E5E7EB;">
                @endif
                @endforeach
            </div>
            
            <div class="mt-10 pt-6 flex justify-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-xl flex items-center justify-center w-full sm:w-auto transition-colors">
                    Selesai & Kumpulkan Tes
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>

        <!-- Modal Confirmation -->
        <div x-show="showConfirmModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <div x-show="showConfirmModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-neutral-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                     @click="showConfirmModal = false" aria-hidden="true"></div>

                <div x-show="showConfirmModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4" 
                     style="margin: auto; display: inline-block; text-align: left; background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); transition: all 0.3s; max-width: 32rem; width: 100%; z-index: 50; position: relative;">
                    
                    <div style="padding: 1.5rem;">
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center; height: 3rem; width: 3rem; border-radius: 9999px; background-color: #FEF3C7;">
                                <svg style="height: 1.5rem; width: 1.5rem; color: #D97706;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div style="padding-top: 0.25rem;">
                                <h3 style="font-size: 1.125rem; line-height: 1.75rem; font-weight: 600; color: #111827; margin: 0; margin-bottom: 0.5rem;" id="modal-title">
                                    Selesaikan Ujian?
                                </h3>
                                <p style="font-size: 0.875rem; line-height: 1.25rem; color: #6B7280; margin: 0;">
                                    Apakah Anda yakin ingin menyelesaikan dan mengumpulkan tes ini? Jawaban Anda tidak bisa diubah lagi setelah ini.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background-color: #F9FAFB; padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #E5E7EB;">
                        <button type="button" @click="showConfirmModal = false" style="display: inline-flex; justify-content: center; align-items: center; border-radius: 0.75rem; border: 1px solid #D1D5DB; background-color: white; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); cursor: pointer;">
                            Batal
                        </button>
                        <button type="button" @click="$refs.examForm.submit()" style="display: inline-flex; justify-content: center; align-items: center; border-radius: 0.75rem; border: 1px solid transparent; background-color: #2563EB; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: white; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); cursor: pointer;">
                            Ya, Kumpulkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('examProgress', () => ({
                total: 0,
                answered: 0,
                initProgress(total, answered) {
                    this.total = total;
                    this.answered = answered;
                },
                updateProgress() {
                    this.answered = document.querySelectorAll('input[type="radio"]:checked').length;
                }
            }));
            
            Alpine.data('questionCard', (answerId, initialSelected) => ({
                selected: initialSelected,
                status: 'idle',
                saveTimeout: null,
                
                async saveAnswer(opt) {
                    this.status = 'saving';
                    try {
                        const response = await fetch("{{ route('registration.exam.save-answer') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                exam_answer_id: answerId,
                                selected_option: opt
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            this.status = 'saved';
                            window.dispatchEvent(new CustomEvent('answer-saved'));
                            
                            clearTimeout(this.saveTimeout);
                            this.saveTimeout = setTimeout(() => {
                                this.status = 'idle';
                            }, 3000);
                        } else {
                            this.status = 'error';
                        }
                    } catch (error) {
                        this.status = 'error';
                        console.error("Error saving answer:", error);
                    }
                }
            }));
        });
    </script>
    
    @endif
</div>
</div>
</section>
@endsection
