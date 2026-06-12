<x-layouts.app>
    <div class="space-y-8">
        <!-- Hero Slider / Carousel -->
        <x-hero-carousel />

        <!-- Filterable Catalog Grid -->
        <livewire:product-grid />
    </div>

    <!-- Cyberpunk Announcement Modal -->
    @php
        $popupSettingsPath = storage_path('app/popup_settings.json');
        $popupSettings = [
            'is_active' => false,
            'type' => 'both',
            'title' => 'SYSTEM_NOTIFICATION',
            'text' => '',
            'image_url' => ''
        ];
        if (file_exists($popupSettingsPath)) {
            $popupSettings = json_decode(file_get_contents($popupSettingsPath), true);
        }
    @endphp

    @if($popupSettings['is_active'])
        <div 
            x-data="{ showPopup: false }"
            x-init="
                setTimeout(() => {
                    showPopup = true;
                }, 800);
            "
            x-show="showPopup"
            @keydown.escape.window="showPopup = false"
            class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div 
                @click.outside="showPopup = false"
                class="bg-[#181818] border border-cyan-500/30 rounded-lg max-w-md w-full overflow-hidden shadow-[0_0_50px_rgba(6,182,212,0.2)] flex flex-col font-mono text-xs relative"
            >
                <!-- Scanner lines -->
                <div class="absolute inset-0 pointer-events-none bg-gradient-to-b from-transparent via-cyan-500/3 to-transparent h-[200%] animate-[pulse_3s_infinite]"></div>
 
                <!-- Modal Window Header (IDE style) -->
                <div class="h-9 border-b border-[#2b2b2b] bg-[#202020] px-4 flex items-center justify-between text-[#858585] shrink-0 select-none">
                    <div class="flex items-center gap-1.5">
                        <span class="window-dot dot-close cursor-pointer" @click="showPopup = false"></span>
                        <span class="window-dot dot-min"></span>
                        <span class="window-dot dot-max"></span>
                        <span class="ml-2 text-[10px] text-slate-500">// {{ $popupSettings['title'] }}</span>
                    </div>
                    <button @click="showPopup = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
 
                <!-- Modal Content -->
                <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto relative z-10">
                    @if($popupSettings['type'] === 'image' || $popupSettings['type'] === 'both')
                        @if(!empty($popupSettings['image_url']))
                            <div class="rounded-md border border-white/5 overflow-hidden bg-slate-900 shadow-md">
                                <img src="{{ $popupSettings['image_url'] }}" alt="Announcement Image" class="w-full h-auto object-cover max-h-56">
                            </div>
                        @endif
                    @endif
 
                    @if($popupSettings['type'] === 'text' || $popupSettings['type'] === 'both')
                        <div class="space-y-2 leading-relaxed text-[#cccccc]">
                            <div class="text-[9px] text-[#569cd6] font-bold uppercase">// SYS_CONTENT_STREAM</div>
                            <p class="whitespace-pre-line text-xs font-sans text-slate-300">{{ $popupSettings['text'] }}</p>
                        </div>
                    @endif
                </div>
 
                <!-- Modal Footer -->
                <div class="p-3 border-t border-[#2b2b2b] bg-[#1a1a1a] flex justify-end gap-2 shrink-0 relative z-10">
                    <button 
                        @click="showPopup = false"
                        class="px-4 py-1.5 bg-cyan-600 hover:bg-cyan-500 active:bg-cyan-700 text-white font-bold text-[10px] uppercase rounded shadow-[0_0_10px_rgba(6,182,212,0.3)] transition-all flex items-center gap-1.5"
                    >
                        <span>EXEC_DISMISS (AKSEPTASI)</span>
                        <span class="text-[8px] bg-cyan-800 px-1 py-0.2 rounded font-mono">ESC</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
