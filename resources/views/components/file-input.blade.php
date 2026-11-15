{{--
    Input berkas standar. Tombol "Pilih file" tampil seperti tombol sekunder.
    <x-file-input id="logo" name="logo" accept="image/*" :error="$errors->has('logo')" />
--}}
@props(['error' => false])

<input {{ $attributes->merge(['type' => 'file', 'class' => 'block w-full min-w-0 rounded-md text-sm text-neutral-600 cursor-pointer file:mr-3 file:h-11 md:file:h-9 file:cursor-pointer file:rounded-md file:border file:border-solid file:bg-white file:px-4 file:text-sm file:font-semibold file:text-neutral-700 hover:file:bg-neutral-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/20 disabled:cursor-not-allowed disabled:opacity-50 ' . ($error ? 'file:border-error' : 'file:border-neutral-300')]) }}>
