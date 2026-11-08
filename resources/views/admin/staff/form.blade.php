@extends('layouts.admin')
@section('title', ($staff->exists ? 'Edit Akun Staf' : 'Tambah Akun Staf') . ' — Admin PMB YPIB')
@section('page-title', $staff->exists ? 'Edit Akun Staf' : 'Tambah Akun Staf')

@section('content')

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.staff.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-neutral-900 tracking-tight">{{ $staff->exists ? 'Edit Akun Staf' : 'Tambah Akun Staf' }}</h1>
            <p class="mt-0.5 text-sm text-neutral-400">Peran menentukan menu yang bisa diakses, kampus membatasi data yang terlihat.</p>
        </div>
    </div>

    <x-card class="p-4 md:p-8">
        <form action="{{ $staff->exists ? route('admin.staff.update', $staff->id) : route('admin.staff.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if($staff->exists) @method('PUT') @endif

            <div class="md:p-6 md:bg-neutral-50 md:rounded-xl md:border md:border-neutral-200 space-y-5">
                <div>
                    <x-input-label for="name" value="Nama Lengkap" required="true" />
                    <x-text-input type="text" id="name" name="name" :value="old('name', $staff->name)" required autofocus :error="$errors->has('name')" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" required="true" />
                    <x-text-input type="email" id="email" name="email" :value="old('email', $staff->email)" required autocomplete="username" :error="$errors->has('email')" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="role" value="Peran" required="true" />
                        <x-select id="role" name="role" required :error="$errors->has('role')">
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $staff->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('role')" />
                    </div>

                    <div>
                        <x-input-label for="campus_id" value="Kampus" />
                        <x-select id="campus_id" name="campus_id" :error="$errors->has('campus_id')">
                            <option value="">Semua kampus</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" @selected((string) old('campus_id', $staff->campus_id) === (string) $campus->id)>{{ $campus->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('campus_id')" />
                    </div>
                </div>

                <p class="text-xs text-neutral-400">Akun tanpa kampus dapat melihat dan mengelola semua kampus.</p>
            </div>

            @unless($staff->exists)
                <div class="mt-6 md:p-6 md:bg-neutral-50 md:rounded-xl md:border md:border-neutral-200 space-y-5">
                    <div class="text-sm font-bold text-neutral-900">Password Awal</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="password" value="Password" required="true" />
                            <x-text-input type="password" id="password" name="password" required autocomplete="new-password" :error="$errors->has('password')" />
                            <x-input-error :messages="$errors->get('password')" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="Konfirmasi Password" required="true" />
                            <x-text-input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
                        </div>
                    </div>
                </div>
            @endunless

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-8">
                <a href="{{ route('admin.staff.index') }}" class="decoration-none">
                    <x-button type="button" color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">
                    <span x-show="!submitting">Simpan</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin motion-reduce:animate-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>

    @if($staff->exists)
        <x-card id="reset-password" class="p-6 md:p-8 mt-6">
            <form action="{{ route('admin.staff.reset-password', $staff->id) }}" method="POST"
                  data-confirm="Reset password akun ini? Sesi login akun ini akan diakhiri.">
                @csrf
                <div class="text-sm font-bold text-neutral-900">Reset Password</div>
                <p class="mt-0.5 mb-5 text-xs text-neutral-400">Password lama diganti dan semua sesi login akun ini diakhiri.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="reset_password" value="Password Baru" required="true" />
                        <x-text-input type="password" id="reset_password" name="password" required autocomplete="new-password" :error="$errors->has('password')" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>
                    <div>
                        <x-input-label for="reset_password_confirmation" value="Konfirmasi Password Baru" required="true" />
                        <x-text-input type="password" id="reset_password_confirmation" name="password_confirmation" required autocomplete="new-password" />
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <x-button type="submit" color="neutral" variant="outline">Reset Password</x-button>
                </div>
            </form>
        </x-card>
    @endif
</div>
@endsection
