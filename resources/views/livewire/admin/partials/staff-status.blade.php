{{-- Status akun staf. Akun sendiri tidak bisa dinonaktifkan; menonaktifkan memutus sesi seketika, jadi minta konfirmasi. --}}
@if($account->id === auth()->id())
    <x-badge-status :active="$account->is_active" />
@elseif($account->is_active)
    <div data-confirm="{{ 'Nonaktifkan akun ' . $account->name . '? Sesi login akun ini akan diakhiri.' }}">
        <x-badge-status :active="true" :toggle-action="route('admin.staff.toggle', $account->id)" />
    </div>
@else
    <x-badge-status :active="false" :toggle-action="route('admin.staff.toggle', $account->id)" />
@endif
