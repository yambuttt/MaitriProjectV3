<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component
{
    public string $search = '';
    public array $searchResults = [];

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $this->search . '%')
            ->select('id', 'name', 'slug', 'developer', 'logo')
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->searchResults = [];
    }
};
?>

<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 w-full border-b border-ide bg-ide-sidebar select-none font-mono">
    
    <!-- Top window control dots & status path (Code Editor Vibe) -->
    <div class="h-8 border-b border-ide px-4 flex items-center justify-between text-[11px] text-[#858585]">
        <!-- Window controls -->
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="window-dot dot-close"></span>
            <span class="window-dot dot-min"></span>
            <span class="window-dot dot-max"></span>
            <span class="ml-2 hidden sm:inline text-slate-500">// Workspace: MaitriProjectV3</span>
        </div>

        <!-- Current File / Code view path -->
        <div class="truncate px-4">
            app/Http/Livewire/<span class="text-[#cccccc]">TopupController.php</span>
        </div>

        <!-- Code status / branch -->
        <div class="hidden sm:block shrink-0 text-slate-500 font-bold">
            git(main) // branch
        </div>
    </div>

    <!-- Active Editor Tabs & Command Palette Search Row -->
    <div class="container mx-auto px-4 md:px-6 h-14 flex items-center justify-between gap-4">
        
        <!-- Left side: Logo & Tabs -->
        <div class="flex items-center gap-4 self-stretch min-w-0">
            <!-- Logo -->
            <a href="/" wire:navigate class="flex items-center gap-1.5 shrink-0 hover:opacity-90 transition-opacity">
                <span class="text-cyan-400 font-bold font-mono">&lt;</span>
                <span class="font-space font-bold tracking-wider text-white text-sm">MAITRI_STORE</span>
                <span class="text-cyan-400 font-bold font-mono">/&gt;</span>
            </a>

            <!-- Tabs listing (Hidden on mobile/tablet) -->
            <div class="hidden md:flex items-center gap-1 self-stretch overflow-x-auto scrollbar-none text-xs border-l border-ide pl-4 h-full">
                <!-- Home Tab -->
                <a href="/" wire:navigate class="flex items-center gap-2 px-4 h-full border-r border-ide transition-all duration-150 {{ Request::is('/') ? 'bg-ide-editor border-t-2 border-t-[#007acc] text-white' : 'text-slate-500 hover:bg-white/5 hover:text-slate-300' }}">
                    <span class="text-amber-500 font-bold font-mono">{}</span>
                    <span>home.json</span>
                </a>

                <!-- Tracker Tab -->
                <a href="/tracker" wire:navigate class="flex items-center gap-2 px-4 h-full border-r border-ide transition-all duration-150 {{ Request::is('tracker') ? 'bg-ide-editor border-t-2 border-t-[#007acc] text-white' : 'text-slate-500 hover:bg-white/5 hover:text-slate-300' }}">
                    <span class="text-sky-400 font-bold font-mono">py</span>
                    <span>order_tracker.py</span>
                </a>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <!-- Admin Tab -->
                    <a href="/admin/dashboard" wire:navigate class="flex items-center gap-2 px-4 h-full border-r border-ide transition-all duration-150 {{ Request::is('admin/dashboard') ? 'bg-ide-editor border-t-2 border-t-[#007acc] text-white' : 'text-slate-500 hover:bg-white/5 hover:text-slate-300' }}">
                        <span class="text-rose-500 font-bold font-mono">rs</span>
                        <span>admin_dashboard.rs</span>
                    </a>
                @endif

                <!-- Other tabs simulated -->
                <div class="hidden lg:flex items-center gap-2 px-4 h-full border-r border-ide text-slate-600 select-none">
                    <span class="text-emerald-500">go</span>
                    <span>payment_gateway.go</span>
                </div>
                <div class="hidden lg:flex items-center gap-2 px-4 h-full border-r border-ide text-slate-600 select-none">
                    <span class="text-[#e06c75]">sh</span>
                    <span>checkout_sequence.sh</span>
                </div>
            </div>
        </div>

        <!-- Right side: Search, WA, Mobile menu -->
        <div class="flex items-center gap-2.5 justify-end flex-1 max-w-md">
            <!-- Mobile Search Shortcut -->
            <button 
                @click="mobileMenuOpen = true; $nextTick(() => $refs.mobileSearchInput.focus())"
                class="flex md:hidden flex-1 items-center gap-1.5 px-2.5 py-1.5 rounded bg-[#252526] border border-ide text-slate-500 hover:text-slate-300 text-[10px] min-w-0"
            >
                <svg class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="truncate">Cari game...</span>
            </button>

            <!-- Search bar styled like Ctrl+P Command Palette (Desktop) -->
            <div class="relative w-full max-w-[240px] hidden md:block" @click.outside="$wire.clearSearch()">
                <div class="relative">
                    <input 
                        wire:model.live="search" 
                        type="text" 
                        placeholder="Ctrl+P (Cari game...)" 
                        class="w-full h-8 pl-8 pr-4 rounded bg-[#252526] border border-ide text-xs placeholder-slate-600 focus:outline-none focus:border-[#007acc] text-white transition-all shadow-inner"
                    >
                    <svg class="absolute left-2.5 top-2.5 w-3 h-3 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>

                    <!-- Clear -->
                    <button x-show="$wire.search.length > 0" @click="$wire.clearSearch()" class="absolute right-2 top-2 text-slate-500 hover:text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search Dropdown Palette -->
                <div 
                    x-show="$wire.searchResults.length > 0" 
                    x-transition:enter="transition ease-out duration-100 transform"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-75 transform"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                    class="absolute top-10 left-0 right-0 bg-ide-sidebar border border-ide rounded-lg p-1.5 z-50 shadow-2xl"
                    style="display: none;"
                >
                    <div class="text-[9px] font-bold text-slate-500 uppercase px-2.5 py-1 tracking-wider">// COMMAND_PALETTE_MATCH</div>
                    <div class="space-y-0.5 mt-1">
                        @foreach($searchResults as $res)
                            <a href="/product/{{ $res['slug'] }}" wire:navigate @click="$wire.clearSearch()" class="flex items-center gap-2.5 p-2 rounded hover:bg-[#2a2d2e] transition-colors text-xs">
                                <img src="{{ $res['logo'] }}" alt="{{ $res['name'] }}" class="w-6 h-6 rounded bg-slate-900 object-cover border border-white/5 shrink-0">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-200 truncate">{{ $res['name'] }}</div>
                                    <div class="text-[9px] text-slate-500 truncate">developer: {{ $res['developer'] }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- WhatsApp Support button (IDE icon style) -->
            <a href="#" class="p-2 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-emerald-500 shadow-md transition-all shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 0 0 1.333 4.982L2 22l5.233-1.371a9.994 9.994 0 0 0 4.78 1.22h.005c5.502 0 9.981-4.479 9.982-9.987.001-2.67-1.037-5.18-2.92-7.062A9.925 9.925 0 0 0 12.012 2zm5.748 13.917c-.315.89-1.536 1.63-2.11 1.74-.537.104-1.238.188-3.415-.715-2.783-1.157-4.583-3.993-4.722-4.178-.139-.185-1.127-1.498-1.127-2.856 0-1.358.708-2.023.96-2.294.253-.27.553-.338.738-.338.185 0 .37.003.53.01.17.007.397-.065.62.48.232.568.795 1.942.862 2.078.067.135.112.293.022.473-.09.18-.135.293-.27.45-.135.158-.283.35-.405.47-.137.135-.28.283-.12.557.16.27.708 1.168 1.52 1.892.812.724 1.492.948 1.702 1.053.21.105.333.09.458-.052.124-.143.53-.616.67-.826.142-.21.284-.175.48-.103.195.07.124.088 1.488.77.218.109.363.163.42.278.057.116.057.674-.258 1.564z"/>
                </svg>
            </a>

            <!-- Mobile menu trigger -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded bg-[#252526] border border-ide text-slate-400 hover:text-white shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer tabs list -->
    <div 
        x-show="mobileMenuOpen" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden bg-[#181818] border-t border-ide px-4 py-4 space-y-3 font-mono text-xs text-slate-400"
        style="display: none;"
    >
        <div class="relative w-full" @click.outside="$wire.clearSearch()">
            <input 
                x-ref="mobileSearchInput"
                wire:model.live="search" 
                type="text" 
                placeholder="Cari game..." 
                class="w-full h-9 pl-9 pr-4 rounded bg-[#252526] border border-ide text-xs focus:outline-none focus:border-[#007acc] text-white"
            >
            <svg class="absolute left-3 top-3 w-3 h-3 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>

            <!-- Search Results Dropdown -->
            <div 
                x-show="$wire.searchResults.length > 0" 
                class="absolute top-11 left-0 right-0 bg-ide-sidebar border border-ide rounded-lg p-1 z-50 shadow-2xl"
                style="display: none;"
            >
                @foreach($searchResults as $res)
                    <a href="/product/{{ $res['slug'] }}" wire:navigate @click="mobileMenuOpen = false; $wire.clearSearch()" class="flex items-center gap-2 p-2 rounded hover:bg-[#2a2d2e]">
                        <img src="{{ $res['logo'] }}" alt="{{ $res['name'] }}" class="w-6 h-6 rounded bg-slate-900 object-cover border border-white/5 shrink-0">
                        <div class="truncate text-slate-300">{{ $res['name'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <nav class="flex flex-col gap-1 font-medium text-xs">
            <a href="/" wire:navigate @click="mobileMenuOpen = false" class="p-2.5 rounded hover:bg-white/5 text-slate-300 hover:text-white flex items-center justify-between">
                <span>// home.json</span>
                <span class="text-amber-500 font-bold font-mono">{}</span>
            </a>
            <a href="/tracker" wire:navigate @click="mobileMenuOpen = false" class="p-2.5 rounded hover:bg-white/5 text-slate-300 hover:text-white flex items-center justify-between">
                <span>// order_tracker.py</span>
                <span class="text-sky-400 font-bold font-mono">py</span>
            </a>
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="/admin/dashboard" wire:navigate @click="mobileMenuOpen = false" class="p-2.5 rounded hover:bg-white/5 text-slate-300 hover:text-white flex items-center justify-between">
                    <span>// admin_dashboard.rs</span>
                    <span class="text-rose-500 font-bold font-mono">rs</span>
                </a>
            @endif
        </nav>
    </div>

</header>