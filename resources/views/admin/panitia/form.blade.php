@extends('layouts.admin')
@section('title', ($panitia->exists ? 'Edit Panitia' : 'Tambah Panitia') . ' — Admin PMB YPIB')
@section('page-title', $panitia->exists ? 'Edit Panitia' : 'Tambah Panitia')

@section('content')

<div class="max-w-3xl">
    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.panitia.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">{{ $panitia->exists ? 'Edit Panitia' : 'Tambah Panitia' }}</h1>
            <p class="text-sm text-neutral-500">Isi formulir di bawah ini untuk mengelola data panitia.</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="mb-8">
                <label class="block text-sm font-semibold text-neutral-900 mb-3">Informasi Akun</label>
                <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">
                    
                    <div>
                        <x-input-label for="name" value="Nama Lengkap" required="true" />
                        <x-text-input id="name" name="name" type="text" :value="old('name', $panitia->name)" required autofocus autocomplete="name" :error="$errors->has('name')" />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" required="true" />
                        <x-text-input id="email" name="email" type="email" :value="old('email', $panitia->email)" required autocomplete="username" :error="$errors->has('email')" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-neutral-900 mb-3">Keamanan (Password)</label>
                <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="password" value="Password" :required="!$panitia->exists" />
                            <x-text-input id="password" name="password" type="password" :required="!$panitia->exists" autocomplete="new-password" :error="$errors->has('password')" />
                            @if($panitia->exists)
                                <div class="text-xs text-neutral-400 mt-1.5">Kosongkan jika tidak ingin mengubah password.</div>
                            @endif
                            <x-input-error :messages="$errors->get('password')" />
                        </div>
                        
                        <div>
                            <x-input-label for="password_confirmation" value="Konfirmasi Password" :required="!$panitia->exists" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" :required="!$panitia->exists" autocomplete="new-password" :error="$errors->has('password_confirmation')" />
                            <x-input-error :messages="$errors->get('password_confirmation')" />
                        </div>
                    </div>

                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-8">
                <a href="{{ route('admin.panitia.index') }}" class="decoration-none">
                    <x-button type="button" color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="primary">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>

@endsection
