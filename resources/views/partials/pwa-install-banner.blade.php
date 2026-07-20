@php
    $texts = [
        'public' => 'Install aplikasi PMB YPIB untuk akses lebih cepat & mudah!',
        'admin' => 'Install Admin PMB YPIB ke HP untuk akses lebih cepat',
        'panitia' => 'Install Panitia PMB YPIB untuk akses scan QR lebih cepat',
    ];
    $bannerText = $texts[$section] ?? $texts['public'];
@endphp

<div x-data="pwaInstallBanner('{{ $section }}')" 
     x-show="showBanner"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-10"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-10"
     style="display: none;"
     class="fixed bottom-0 left-0 right-0 z-[9999] p-4 pb-[max(env(safe-area-inset-bottom),16px)] md:hidden">
    
    <div class="bg-primary-600 text-white rounded-xl shadow-2xl p-4 flex flex-col gap-3 border border-primary-500 relative">
        <button @click="dismissBanner" class="absolute top-2 right-2 text-white/70 hover:text-white" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        
        <div class="flex items-center gap-3 pr-6">
            <div class="flex-shrink-0 bg-white p-2 rounded-lg">
                <img src="{{ asset('images/favicon.png') }}" class="w-8 h-8 object-contain" alt="Icon">
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium leading-tight">
                    {{ $bannerText }}
                </p>
            </div>
        </div>

        <template x-if="isAndroid">
            <button @click="installPwa" class="w-full bg-white text-primary-700 font-bold py-2 px-4 rounded-lg text-sm transition hover:bg-neutral-50 active:bg-neutral-100">
                Install Sekarang
            </button>
        </template>
        
        <template x-if="isIOS">
            <div class="bg-primary-800/60 rounded-lg p-3 text-xs leading-relaxed flex items-center gap-2">
                <span>Tap tombol</span>
                <svg class="w-4 h-4 inline-block -mt-1" viewBox="0 0 50 50" fill="currentColor">
                    <path d="M25 2.11L14.62 12.5h5.45v19.46h9.86V12.5h5.45L25 2.11zM11.66 18.06v27.28h26.68V18.06h-5.26v4.61h.65v18.06H16.27V22.67h.65v-4.61h-5.26z"/>
                </svg>
                <span>di bawah, lalu pilih <strong>Add to Home Screen</strong></span>
            </div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pwaInstallBanner', (section) => ({
        showBanner: false,
        isAndroid: false,
        isIOS: false,
        deferredPrompt: null,
        
        init() {
            // Deteksi standalone mode
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone || document.referrer.includes('android-app://');
            if (isStandalone) return;

            // Cek localstorage dismiss
            const dismissedAt = localStorage.getItem(`pwa_dismissed_${section}`);
            if (dismissedAt) {
                const dismissTime = new Date(parseInt(dismissedAt)).getTime();
                const now = new Date().getTime();
                const fourteenDays = 14 * 24 * 60 * 60 * 1000;
                if (now - dismissTime < fourteenDays) {
                    return; // Masih dalam masa cooldown 14 hari
                }
            }

            // Deteksi OS dari User Agent
            const ua = navigator.userAgent.toLowerCase();
            const isIOS = /ipad|iphone|ipod/.test(ua) && !window.MSStream;
            const isAndroid = /android/.test(ua);

            if (isIOS) {
                this.isIOS = true;
                this.triggerDelay();
            } else if (isAndroid) {
                // Tunggu event beforeinstallprompt
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.isAndroid = true;
                    this.triggerDelay();
                });
            }
        },

        triggerDelay() {
            // Munculkan setelah 2.5 detik
            setTimeout(() => {
                this.showBanner = true;
            }, 2500);
        },

        installPwa() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                this.deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        this.showBanner = false;
                    }
                    this.deferredPrompt = null;
                });
            }
        },

        dismissBanner() {
            this.showBanner = false;
            localStorage.setItem(`pwa_dismissed_${section}`, new Date().getTime().toString());
        }
    }));
});
</script>
