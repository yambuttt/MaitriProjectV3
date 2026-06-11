<?php

use Livewire\Component;
use App\Models\Category;
use App\Models\Product;

new class extends Component
{
    public ?int $selectedCategoryId = null;

    public function selectCategory(?int $categoryId)
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function getCategories()
    {
        return Category::select('id', 'name', 'slug', 'icon')->get();
    }

    public function getProducts()
    {
        $query = Product::with('category')->select('id', 'category_id', 'name', 'slug', 'developer', 'logo', 'banner', 'is_popular');
        
        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        return $query->get();
    }

    public function getPopularProducts()
    {
        return Product::where('is_popular', true)->select('id', 'name', 'slug', 'developer', 'logo', 'banner')->get();
    }
};
?>

<div class="space-y-12 my-8 relative z-10 font-mono text-xs text-[#d4d4d4]">
    
    <!-- Popular Products Section (Populer Sekarang) -->
    <div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)" class="space-y-4">
        
        <div class="flex items-center gap-2 mb-3">
            <span class="text-emerald-500 font-bold">{}</span>
            <span class="text-slate-200 font-bold uppercase tracking-wider">popular_products.json</span>
            <span class="text-slate-600 font-bold">// active_catalog</span>
        </div>

        <!-- 2 Columns on Mobile, 4 Columns on Desktop -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($this->getPopularProducts() as $index => $pop)
                <a 
                    href="/product/{{ $pop->slug }}" 
                    class="group relative overflow-hidden rounded-lg border border-ide bg-[#181818] p-3 flex items-center gap-3 transition-all duration-200 hover:bg-[#252526] hover:border-[#007acc] hover:shadow-[0_0_15px_rgba(0,122,204,0.2)] active:scale-98"
                    x-show="loaded"
                    x-transition:enter="transition ease-out duration-300 delay-[{{ $index * 30 }}ms]"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    <!-- File tab indicator line on hover -->
                    <div class="absolute left-0 top-0 bottom-0 w-[3px] bg-transparent group-hover:bg-[#007acc] transition-colors"></div>

                    <!-- Mini Logo Container -->
                    <div class="w-11 h-11 rounded-lg bg-slate-900 border border-ide flex items-center justify-center p-1 group-hover:scale-105 transition-transform shrink-0 shadow-md">
                        @if($pop->logo)
                            <img src="{{ $pop->logo }}" alt="{{ $pop->name }}" class="w-full h-full object-cover rounded">
                        @else
                            <span class="text-lg">🎮</span>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-[#cccccc] group-hover:text-[#007acc] transition-colors truncate tracking-wide text-xs">{{ $pop->name }}</h4>
                        <p class="text-[9px] text-slate-500 truncate mt-0.5">// dev: {{ strtolower(str_replace(' ', '_', $pop->developer)) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Category Tabs & Product Catalog -->
    <div class="space-y-6">
        
        <!-- Category Filter Row (styled like a programming array declaration) -->
        <div class="flex flex-wrap items-center gap-2 border-b border-ide pb-4 text-[10px] sm:text-xs">
            <span class="text-purple-400 font-bold">const</span>
            <span class="text-[#9cdcfe]">categories</span>
            <span class="text-[#d4d4d4]">=</span>
            <span class="text-slate-500 font-bold">[</span>

            <button 
                wire:click="selectCategory(null)"
                class="px-2.5 py-1 rounded transition-all duration-150 active:scale-95 {{ is_null($selectedCategoryId) ? 'bg-[#007acc]/20 text-[#4fc1ff] font-bold border border-[#007acc]/40' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }}"
            >
                "All"
            </button>

            @foreach($this->getCategories() as $cat)
                <span class="text-slate-600">,</span>
                <button 
                    wire:click="selectCategory({{ $cat->id }})"
                    class="px-2.5 py-1 rounded transition-all duration-150 active:scale-95 {{ $selectedCategoryId === $cat->id ? 'bg-[#007acc]/20 text-[#4fc1ff] font-bold border border-[#007acc]/40' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }}"
                >
                    "{{ strtolower(str_replace(' & ', '_', $cat->name)) }}"
                </button>
            @endforeach
            <span class="text-slate-500 font-bold">];</span>
        </div>

        <!-- Catalog Product Grid: 3 Columns on Mobile, 4 Columns on SM, 5 Columns on Desktop -->
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3" wire:loading.class="opacity-50">
            @forelse($this->getProducts() as $prod)
                <a 
                    href="/product/{{ $prod->slug }}" 
                    class="group relative aspect-[10/14] w-full rounded-lg overflow-hidden border border-ide bg-[#181818] flex flex-col justify-end transition-all duration-300 hover:border-[#007acc] hover:shadow-[0_0_20px_rgba(0,122,204,0.2)] hover:-translate-y-1 active:scale-95"
                >
                    <!-- Tall Background Card Image (Seeded cover banners) -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center group-hover:scale-108 transition-transform duration-500 ease-out pointer-events-none" 
                        style="background-image: url('{{ $prod->banner ?? 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=400&q=80' }}')"
                    ></div>

                    <!-- Bottom linear shadow mask overlay for text contrast -->
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0e0e10] via-[#0e0e10]/30 to-transparent pointer-events-none"></div>

                    <!-- Tab Header decoration on card -->
                    <div class="absolute top-0 inset-x-0 h-6 bg-[#181818]/90 border-b border-ide flex items-center justify-between px-2 text-[8px] text-slate-500 z-10 select-none">
                        <span class="truncate pr-2">// {{ strtolower(str_replace('-', '_', $prod->slug)) }}.go</span>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span>
                        </div>
                    </div>

                    <!-- Custom Reference Badges styled like cybernetic code tags -->
                    <div class="absolute top-8 left-2 z-10">
                        @if($prod->slug === 'mobile-legends')
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-500 text-[7px] font-bold tracking-wider border border-amber-500/30">IDN_GATEWAY</span>
                        @elseif($prod->slug === 'mlbb-paket-irit')
                            <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[7px] font-bold tracking-wider border border-emerald-500/30">BUNDLE_RATE</span>
                        @elseif($prod->slug === 'pubg-mobile')
                            <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 text-[7px] font-bold tracking-wider border border-blue-500/30">GLOBAL_UC</span>
                        @elseif($prod->slug === 'roblox')
                            <span class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400 text-[7px] font-bold tracking-wider border border-rose-500/30 animate-pulse">DISCOUNT_VAL</span>
                        @elseif(str_contains($prod->slug, 'joki'))
                            <span class="px-1.5 py-0.5 rounded bg-pink-500/10 text-pink-400 text-[7px] font-bold tracking-wider border border-pink-500/30">BOOST_ACC</span>
                        @else
                            <span class="px-1.5 py-0.5 rounded bg-slate-900/50 text-slate-300 text-[7px] font-bold tracking-wider border border-white/10">PRODUCT_SL</span>
                        @endif
                    </div>

                    <!-- Card details overlay (glass base at bottom) -->
                    <div class="relative z-10 w-full p-2 bg-[#181818]/90 border-t border-ide flex flex-col items-center text-center">
                        <h4 class="font-bold text-[#cccccc] text-[10px] group-hover:text-[#007acc] transition-colors truncate w-full tracking-wide">{{ $prod->name }}</h4>
                        <p class="text-[8px] text-slate-500 truncate w-full mt-0.5 font-mono uppercase tracking-wider">// {{ $prod->developer }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 font-mono text-xs">
                    <span class="text-3xl block mb-2 font-bounce">👾</span>
                    // ERR: PRODUCT_MAPPING_EMPTY
                </div>
            @endforelse
        </div>

    </div>

</div>