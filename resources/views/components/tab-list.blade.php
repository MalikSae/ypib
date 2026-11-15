{{--
    Tab untuk berpindah tampilan dalam satu halaman (pola ARIA tabs). Berbeda dengan x-filter-tab yang menyaring daftar.
    Wadah induk memegang state Alpine "tab":
      <div x-data="{ tab: 'recap' }">
          <x-tab-list aria-label="Bagian komisi">
              <x-tab name="recap" :count="3">Rekap siap cair</x-tab>
              <x-tab name="history">Riwayat pencairan</x-tab>
          </x-tab-list>
          <div id="tab-recap" role="tabpanel" aria-labelledby="tab-btn-recap" x-show="tab === 'recap'">...</div>
          <div id="tab-history" role="tabpanel" aria-labelledby="tab-btn-history" x-show="tab === 'history'" x-cloak>...</div>
      </div>
    Panah kiri/kanan, Home, dan End berpindah tab.
--}}
<div role="tablist"
     x-on:keydown="
        const tabs = [...$el.querySelectorAll('[role=tab]')];
        let i = tabs.indexOf(document.activeElement);
        if (i < 0) return;
        if ($event.key === 'ArrowRight') i = (i + 1) % tabs.length;
        else if ($event.key === 'ArrowLeft') i = (i - 1 + tabs.length) % tabs.length;
        else if ($event.key === 'Home') i = 0;
        else if ($event.key === 'End') i = tabs.length - 1;
        else return;
        $event.preventDefault(); tabs[i].focus(); tabs[i].click();"
     {{ $attributes->merge(['class' => 'flex gap-6 overflow-x-auto shadow-[inset_0_-1px_0_theme(colors.neutral.200)]']) }}>
    {{ $slot }}
</div>
