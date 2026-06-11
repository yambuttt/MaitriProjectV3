<div x-data="{ 
    activeSlide: 0, 
    slides: [
        { 
            file: 'promo_june.py',
            lang: 'python',
            syntax: [
                { line: 1, content: '<span class=&quot;syntax-keyword&quot;>import</span> discount_system' },
                { line: 2, content: '' },
                { line: 3, content: '<span class=&quot;syntax-keyword&quot;>class</span> <span class=&quot;syntax-class&quot;>JuneCampaign</span>:' },
                { line: 4, content: '    title = <span class=&quot;syntax-string&quot;>&quot;PROMO JUNI DISKON HINGGA 10K&quot;</span>' },
                { line: 5, content: '    code = <span class=&quot;syntax-string&quot;>&quot;JUNITOPUP2026&quot;</span>' },
                { line: 6, content: '    status = <span class=&quot;syntax-string&quot;>&quot;ACTIVE_RUNNING&quot;</span>' }
            ]
        },
        { 
            file: 'bonus_robux.go',
            lang: 'go',
            syntax: [
                { line: 1, content: '<span class=&quot;syntax-keyword&quot;>package</span> main' },
                { line: 2, content: '' },
                { line: 3, content: '<span class=&quot;syntax-keyword&quot;>func</span> <span class=&quot;syntax-function&quot;>GetRobuxDiscount</span>() {' },
                { line: 4, content: '    promoTitle := <span class=&quot;syntax-string&quot;>&quot;TOP UP ROBLOX BONUS ROBUX&quot;</span>' },
                { line: 5, content: '    promoCode := <span class=&quot;syntax-string&quot;>&quot;ROBUXBIGDISC&quot;</span>' },
                { line: 6, content: '    <span class=&quot;syntax-function&quot;>InjectRobux</span>(promoCode, <span class=&quot;syntax-number&quot;>5</span> /* seconds */)' },
                { line: 7, content: '}' }
            ]
        },
        { 
            file: 'boost_rank.rs',
            lang: 'rust',
            syntax: [
                { line: 1, content: '<span class=&quot;syntax-keyword&quot;>fn</span> <span class=&quot;syntax-function&quot;>main</span>() {' },
                { line: 2, content: '    <span class=&quot;syntax-keyword&quot;>let</span> title = <span class=&quot;syntax-string&quot;>&quot;JOKI MLBB AUTO WIN STREAK&quot;</span>;' },
                { line: 3, content: '    <span class=&quot;syntax-keyword&quot;>let</span> key = <span class=&quot;syntax-string&quot;>&quot;MYSPECIALJOKI&quot;</span>;' },
                { line: 4, content: '    <span class=&quot;syntax-comment&quot;>// pro player squad will process transaction</span>' },
                { line: 5, content: '    <span class=&quot;syntax-function&quot;>boost_star</span>(key, <span class=&quot;syntax-keyword&quot;>true</span>);' },
                { line: 6, content: '}' }
            ]
        }
    ],
    next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
    prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length },
    init() { setInterval(() => this.next(), 8000) }
}" class="relative w-full overflow-hidden rounded-2xl border border-ide bg-[#181818] shadow-2xl z-20 font-mono">

    <!-- IDE Carousel Title tab bar -->
    <div class="h-8 bg-ide-sidebar border-b border-ide px-4 flex items-center justify-between text-[11px] text-[#858585] select-none">
        <div class="flex items-center gap-2 min-w-0">
            <span class="w-2.5 h-2.5 rounded-full bg-[#007acc] shrink-0"></span>
            <span class="font-bold text-[#cccccc] truncate" x-text="slides[activeSlide].file"></span>
            <span class="text-slate-600 font-bold hidden sm:inline shrink-0">// active_campaign</span>
        </div>
        <div class="flex items-center gap-2 text-slate-500 shrink-0 ml-2">
            <span class="hidden sm:inline">Lines: <span x-text="slides[activeSlide].syntax.length"></span></span>
            <span class="text-green-500 font-bold">● RUNNING</span>
        </div>
    </div>

    <!-- Slides Wrapper -->
    <div class="relative h-[240px] sm:h-[300px] w-full flex items-center px-4 sm:px-12 bg-ide-editor">
        
        <template x-for="(slide, index) in slides" :key="index">
            <div 
                x-show="activeSlide === index"
                x-transition:enter="transition ease-out duration-350 transform"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform absolute inset-y-0 left-4 sm:left-12 right-4 sm:right-12"
                class="w-full flex items-start gap-4 text-xs sm:text-sm leading-relaxed min-w-0"
            >
                <!-- Line numbers left block -->
                <div class="select-none text-right text-slate-600 pr-2 border-r border-ide w-8 font-mono space-y-1 shrink-0">
                    <template x-for="item in slide.syntax">
                        <div x-text="item.line" class="h-6"></div>
                    </template>
                </div>

                <!-- Code body block -->
                <div class="flex-1 min-w-0 space-y-1 overflow-x-auto scrollbar-none pb-2">
                    <template x-for="item in slide.syntax">
                        <div class="h-6 whitespace-nowrap text-[#d4d4d4]" x-html="item.content || '&nbsp;'"></div>
                    </template>
                    
                    <!-- Run Code action simulation inside editor -->
                    <div class="pt-4 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 min-w-0">
                        <div class="flex items-center gap-2 shrink-0">
                            <button class="px-4 py-1.5 rounded bg-[#0e639c] hover:bg-[#1177bb] border border-[#1177bb]/30 text-white text-xs font-bold font-mono tracking-wider active:scale-95 transition-all flex items-center gap-2 shadow shadow-black/40">
                                <span>▶</span>
                                <span>RUN_CODE</span>
                            </button>
                        </div>
                        <span class="text-[10px] text-slate-500 truncate shrink-0">// Output: SUCCESS. Discount mapping initialized.</span>
                    </div>
                </div>

            </div>
        </template>
        
    </div>

    <!-- Navigation Dots & Controls -->
    <div class="absolute bottom-4 left-4 flex items-center gap-2 z-30 font-mono text-[9px] text-[#858585]">
        <template x-for="(slide, index) in slides" :key="index">
            <button 
                @click="activeSlide = index"
                class="h-2 rounded transition-all duration-300"
                :class="activeSlide === index ? 'w-5 bg-[#007acc]' : 'w-2 bg-[#3e3e3e] hover:bg-[#4f4f4f]'"
            ></button>
        </template>
    </div>

    <!-- Navigation Arrows -->
    <div class="absolute right-4 bottom-4 flex items-center gap-1 z-30">
        <button @click="prev()" class="w-7 h-7 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-slate-400 flex items-center justify-center active:scale-90 transition-transform">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button @click="next()" class="w-7 h-7 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-slate-400 flex items-center justify-center active:scale-90 transition-transform">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

</div>
