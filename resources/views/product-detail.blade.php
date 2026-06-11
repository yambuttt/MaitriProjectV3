<x-layouts.app>
    <!-- Back to Catalog Link -->
    <div class="mb-4 relative z-10">
        <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Katalog
        </a>
    </div>

    <!-- Livewire Topup Checkout Component -->
    <livewire:topup-form :product="$product" />
</x-layouts.app>
